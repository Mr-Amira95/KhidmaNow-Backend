<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\ChatRoomResource;
use App\Http\Resources\MessageResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Services\FirestoreService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function index(Request $request)
    {
        $user = $request->user();
        $deletedColumn = $user->user_type === 'provider' ? 'deleted_by_provider_at' : 'deleted_by_user_at';

        $query = ChatRoom::query()
            ->when($user->user_type === 'provider', fn ($q) => $q->where('provider_id', $user->provider->id))
            ->when($user->user_type !== 'provider', fn ($q) => $q->where('user_id', $user->id))
            ->whereNull($deletedColumn)
            ->with(['user', 'provider.user', 'latestMessage'])
            ->withCount(['messages as unread_count' => function ($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)->where('is_read', false);
            }])
            ->latest('last_message_at');

        return $this->paginated(ChatRoomResource::class, $query);
    }

    public function store(StoreChatRequest $request)
    {
        $user = $request->user();

        if ($user->user_type === 'provider') {
            $chatRoom = ChatRoom::firstOrCreate([
                'user_id' => $request->validated('customer_id'),
                'provider_id' => $user->provider->id,
            ]);

            if ($chatRoom->deleted_by_provider_at) {
                $chatRoom->update(['deleted_by_provider_at' => null]);
            }
        } elseif ($user->user_type === 'customer') {
            $chatRoom = ChatRoom::firstOrCreate([
                'user_id' => $user->id,
                'provider_id' => $request->validated('provider_id'),
            ]);

            if ($chatRoom->deleted_by_user_at) {
                $chatRoom->update(['deleted_by_user_at' => null]);
            }
        } else {
            return $this->error('Only clients and providers can start a chat.', 403);
        }

        FirestoreService::upsertChatRoom($chatRoom);

        $chatRoom->load(['user', 'provider.user', 'latestMessage']);

        return $this->success(new ChatRoomResource($chatRoom), 'Chat started.', 201);
    }

    public function messages(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user)) {
            return $this->error('You are not a participant in this chat.', 403);
        }

        $unreadMessageIds = $chatRoom->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->pluck('id');

        if ($unreadMessageIds->isNotEmpty()) {
            $chatRoom->messages()->whereIn('id', $unreadMessageIds)->update(['is_read' => true]);
            FirestoreService::markMessagesRead($chatRoom, $unreadMessageIds->all());
        }

        $query = $chatRoom->messages()->with(['sender', 'call'])->latest();

        return $this->paginated(MessageResource::class, $query);
    }

    public function sendMessage(StoreMessageRequest $request, ChatRoom $chatRoom)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user)) {
            return $this->error('You are not a participant in this chat.', 403);
        }

        $data = [
            'chat_id' => $chatRoom->id,
            'sender_id' => $user->id,
            'message' => $request->validated('message'),
            'media_type' => 'text',
            'media_url' => null,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getMimeType();
            $data['media_type'] = str_starts_with($mime, 'audio/') ? 'audio' : ($this->attachmentType($file) === 'video' ? 'video' : 'image');
            $data['media_url'] = $this->storeUpload($file, 'chats');
        }

        $message = Message::create($data);

        $recipientDeletedColumn = $user->user_type === 'provider' ? 'deleted_by_user_at' : 'deleted_by_provider_at';
        $chatRoom->update([
            'last_message_at' => $message->created_at,
            $recipientDeletedColumn => null,
        ]);

        FirestoreService::writeMessage($message);
        FirestoreService::upsertChatRoom($chatRoom);

        // Send chat notification to the other participant
        $recipientId = $user->user_type === 'provider' ? $chatRoom->user_id : ($chatRoom->provider ? $chatRoom->provider->user_id : null);
        if ($recipientId) {
            \App\Services\NotificationService::send(
                $recipientId,
                'New Message from ' . $user->name,
                $message->message ?? 'Sent an attachment.',
                'chat',
                $chatRoom->id
            );
        }

        $message->load('sender');

        return $this->success(new MessageResource($message), 'Message sent.', 201);
    }

    public function destroy(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user)) {
            return $this->error('You are not a participant in this chat.', 403);
        }

        $chatRoom->update([$chatRoom->deletedAtColumnFor($user) => now()]);

        return $this->success([], 'Chat deleted.');
    }
}
