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

        if ($user->user_type !== 'customer') {
            return $this->error('Only clients can start a chat.', 403);
        }

        $chatRoom = ChatRoom::firstOrCreate([
            'user_id' => $user->id,
            'provider_id' => $request->validated('provider_id'),
        ]);

        if ($chatRoom->deleted_by_user_at) {
            $chatRoom->update(['deleted_by_user_at' => null]);
        }

        $chatRoom->load(['user', 'provider.user', 'latestMessage']);

        return $this->success(new ChatRoomResource($chatRoom), 'Chat started.', 201);
    }

    public function messages(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user)) {
            return $this->error('You are not a participant in this chat.', 403);
        }

        $chatRoom->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $query = $chatRoom->messages()->with('sender')->latest();

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
            $data['media_type'] = str_starts_with($file->getMimeType(), 'audio/') ? 'audio' : 'image';
            $data['media_url'] = $this->storeUpload($file, 'chats');
        }

        $message = Message::create($data);

        $recipientDeletedColumn = $user->user_type === 'provider' ? 'deleted_by_user_at' : 'deleted_by_provider_at';
        $chatRoom->update([
            'last_message_at' => $message->created_at,
            $recipientDeletedColumn => null,
        ]);

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
