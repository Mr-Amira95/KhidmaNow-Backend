<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgoraTokenRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Call;
use App\Services\AgoraTokenBuilder;
use RuntimeException;

/**
 * Re-issues an RTC token for a channel the caller already owns.
 *
 * Channels are never created from client-supplied names here — a token is only
 * ever issued for a `channel_name` that matches an existing Call the requesting
 * user is a participant of (see CallController::start, which mints the channel).
 * This prevents a caller from requesting a token for an arbitrary/unowned channel.
 */
class AgoraTokenController extends Controller
{
    use ApiResponse;

    public function issue(AgoraTokenRequest $request)
    {
        $user = $request->user();
        $channelName = $request->validated('channel_name');

        $call = Call::where('agora_channel', $channelName)->first();
        if (!$call) {
            return $this->error('Unknown channel.', 404);
        }

        if (!$call->chatRoom->hasParticipant($user)) {
            return $this->error('You are not a participant in this call.', 403);
        }

        if ($call->status === 'ended') {
            return $this->error('This call has ended.', 409);
        }

        $expireSeconds = (int) config('services.agora.token_ttl', 3600);
        $userAccount = $request->validated('user_account');
        $uid = $userAccount ? null : (int) ($request->validated('uid') ?? $user->id);

        try {
            $token = $userAccount
                ? AgoraTokenBuilder::buildRtcTokenWithAccount($channelName, $userAccount, $expireSeconds)
                : AgoraTokenBuilder::buildRtcToken($channelName, $uid, $expireSeconds);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 500);
        }

        return $this->success([
            'app_id' => config('services.agora.app_id'),
            'channel_name' => $channelName,
            'uid' => $uid,
            'user_account' => $userAccount,
            'rtc_token' => $token,
            'expire_at' => now()->addSeconds($expireSeconds)->toIso8601String(),
        ], 'Token generated.');
    }
}
