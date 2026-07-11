<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartCallRequest;
use App\Http\Resources\CallResource;
use App\Http\Traits\ApiResponse;
use App\Models\Call;
use App\Models\ChatRoom;
use App\Services\AgoraTokenBuilder;
use Illuminate\Http\Request;
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
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        $call->token = $token;
        $call->uid = $user->id;
        $call->expire_at = now()->addSeconds($expireSeconds);

        return $this->success(new CallResource($call), 'Call started.', 201);
    }

    public function end(Request $request, ChatRoom $chatRoom, Call $call)
    {
        $user = $request->user();
        if (!$chatRoom->hasParticipant($user) || (int) $call->chat_id !== (int) $chatRoom->id) {
            return $this->error('You are not a participant in this call.', 403);
        }

        if ($call->status === 'ended') {
            return $this->success(new CallResource($call), 'Call already ended.');
        }

        $endedAt = now();
        $call->update([
            'status' => 'ended',
            'ended_at' => $endedAt,
            'duration_seconds' => max(0, $endedAt->getTimestamp() - $call->started_at->getTimestamp()),
        ]);

        return $this->success(new CallResource($call), 'Call ended.');
    }
}
