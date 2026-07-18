<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartCallRequest;
use App\Http\Resources\CallResource;
use App\Http\Traits\ApiResponse;
use App\Models\Call;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use App\Services\AgoraTokenBuilder;
use App\Services\FirestoreService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CallController extends Controller
{
    use ApiResponse;

    public function start(StartCallRequest $request, ChatRoom $chatRoom)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user)) {
            return $this->error('You are not a participant in this chat.', 403);
        }

        $channel = 'chat_' . $chatRoom->id . '_' . now()->timestamp;
        $expireSeconds = (int) config('services.agora.token_ttl', 3600);

        try {
            $token = AgoraTokenBuilder::buildRtcToken($channel, $user->id, $expireSeconds);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 500);
        }

        $call = Call::create([
            'chat_id' => $chatRoom->id,
            'initiated_by' => $user->id,
            'call_type' => $request->validated('call_type'),
            'agora_channel' => $channel,
            'status' => 'ringing',
            'started_at' => now(),
        ]);

        $call->token = $token;
        $call->uid = $user->id;
        $call->expire_at = now()->addSeconds($expireSeconds);

        $recipientId = $this->otherParticipantId($chatRoom, $user);
        if ($recipientId) {
            NotificationService::sendCallEvent(
                $recipientId,
                'incoming_call',
                [
                    'call_id' => $call->id,
                    'chat_id' => $chatRoom->id,
                    'call_type' => $call->call_type,
                    'channel' => $call->agora_channel,
                    'caller_id' => $user->id,
                    'caller_name' => $user->name,
                    'caller_avatar' => $this->avatarUrl($user),
                ],
                'Incoming call from ' . $user->name,
                ucfirst($call->call_type) . ' call'
            );
        }

        return $this->success(new CallResource($call), 'Call started.', 201);
    }

    public function accept(Request $request, ChatRoom $chatRoom, Call $call)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user) || (int) $call->chat_id !== (int) $chatRoom->id) {
            return $this->error('You are not a participant in this call.', 403);
        }

        if ((int) $call->initiated_by === (int) $user->id) {
            return $this->error('The caller cannot accept their own call.', 403);
        }

        if ($call->status === 'declined') {
            return $this->error('This call was declined.', 409);
        }

        if ($call->status === 'ended') {
            return $this->error('This call has ended.', 409);
        }

        if ($call->status !== 'ringing') {
            return $this->success(new CallResource($call), 'Call already accepted.');
        }

        $expireSeconds = (int) config('services.agora.token_ttl', 3600);

        try {
            $token = AgoraTokenBuilder::buildRtcToken($call->agora_channel, $user->id, $expireSeconds);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 500);
        }

        $call->update([
            'status' => 'ongoing',
            'accepted_at' => now(),
        ]);

        $call->token = $token;
        $call->uid = $user->id;
        $call->expire_at = now()->addSeconds($expireSeconds);

        NotificationService::sendCallEvent($call->initiated_by, 'call_accepted', [
            'call_id' => $call->id,
            'chat_id' => $chatRoom->id,
        ]);

        return $this->success(new CallResource($call), 'Call accepted.');
    }

    public function reject(Request $request, ChatRoom $chatRoom, Call $call)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user) || (int) $call->chat_id !== (int) $chatRoom->id) {
            return $this->error('You are not a participant in this call.', 403);
        }

        if ((int) $call->initiated_by === (int) $user->id) {
            return $this->error('The caller cannot reject their own call.', 403);
        }

        if ($call->status !== 'ringing') {
            return $this->error('This call can no longer be rejected.', 409);
        }

        $call->update([
            'status' => 'declined',
            'ended_at' => now(),
            'duration_seconds' => 0,
        ]);

        NotificationService::sendCallEvent($call->initiated_by, 'call_declined', [
            'call_id' => $call->id,
            'chat_id' => $chatRoom->id,
        ]);

        return $this->success(new CallResource($call), 'Call declined.');
    }

    public function show(Request $request, ChatRoom $chatRoom, Call $call)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user) || (int) $call->chat_id !== (int) $chatRoom->id) {
            return $this->error('You are not a participant in this call.', 403);
        }

        return $this->success(new CallResource($call), 'Call retrieved.');
    }

    public function end(Request $request, ChatRoom $chatRoom, Call $call)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user) || (int) $call->chat_id !== (int) $chatRoom->id) {
            return $this->error('You are not a participant in this call.', 403);
        }

        if (in_array($call->status, ['ended', 'declined'], true)) {
            return $this->success(new CallResource($call), 'Call already ended.');
        }

        $wasRinging = $call->status === 'ringing';

        $endedAt = now();
        $call->update([
            'status' => 'ended',
            'ended_at' => $endedAt,
            'duration_seconds' => max(0, $endedAt->getTimestamp() - $call->started_at->getTimestamp()),
        ]);

        $duration = $call->duration_seconds;
        $message = Message::create([
            'chat_id' => $chatRoom->id,
            'sender_id' => $call->initiated_by,
            'call_id' => $call->id,
            'message' => ucfirst($call->call_type) . ' call (' . sprintf('%02d:%02d', intdiv($duration, 60), $duration % 60) . ')',
            'media_type' => 'call',
            'media_url' => null,
        ]);

        $chatRoom->update([
            'last_message_at' => $message->created_at,
            'deleted_by_user_at' => null,
            'deleted_by_provider_at' => null,
        ]);

        FirestoreService::writeMessage($message);
        FirestoreService::upsertChatRoom($chatRoom);

        $recipientId = $this->otherParticipantId($chatRoom, $user);
        if ($recipientId) {
            NotificationService::sendCallEvent($recipientId, $wasRinging ? 'call_cancelled' : 'call_ended', [
                'call_id' => $call->id,
                'chat_id' => $chatRoom->id,
            ]);
        }

        return $this->success(new CallResource($call), 'Call ended.');
    }

    /**
     * The user id on "the other side" of this chat room relative to the given user.
     */
    private function otherParticipantId(ChatRoom $chatRoom, User $user): ?int
    {
        if ($user->user_type === 'provider') {
            return $chatRoom->user_id;
        }

        return $chatRoom->provider ? $chatRoom->provider->user_id : null;
    }

    private function avatarUrl(User $user): ?string
    {
        if (!$user->profile_image) {
            return null;
        }

        return str_starts_with($user->profile_image, 'http')
            ? $user->profile_image
            : Storage::disk('public')->url($user->profile_image);
    }
}
