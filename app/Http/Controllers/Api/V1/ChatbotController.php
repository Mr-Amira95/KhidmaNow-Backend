<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatbotMessageRequest;
use App\Http\Requests\StoreChatbotRoomRequest;
use App\Http\Resources\ChatbotMessageResource;
use App\Http\Resources\ChatbotRoomResource;
use App\Http\Traits\ApiResponse;
use App\Models\ChatbotRoom;
use App\Models\User;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    use ApiResponse;

    public function store(StoreChatbotRoomRequest $request)
    {
        $user = $request->user('sanctum');
        $sessionId = $request->validated('session_id');

        $room = ChatbotRoom::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => $user?->id]
        );

        if ($user && !$room->user_id) {
            $room->update(['user_id' => $user->id]);
        }

        return $this->success(new ChatbotRoomResource($room), 'Chatbot session started.', 201);
    }

    public function messages(Request $request, ChatbotRoom $room)
    {
        if (!$this->authorizeRoom($request, $room)) {
            return $this->error('You are not allowed to access this chatbot session.', 403);
        }

        $query = $room->messages()->with(['suggestions.provider.user', 'suggestions.provider.city', 'quotation'])->oldest();

        return $this->paginated(ChatbotMessageResource::class, $query);
    }

    public function sendMessage(StoreChatbotMessageRequest $request, ChatbotRoom $room, ChatbotService $chatbotService)
    {
        if (!$this->authorizeRoom($request, $room)) {
            return $this->error('You are not allowed to access this chatbot session.', 403);
        }

        $user = $request->user('sanctum');

        $result = $chatbotService->reply($room, $request->validated('message'), $user);

        $result['message']->load(['suggestions.provider.user', 'suggestions.provider.city', 'quotation']);

        return $this->success([
            'message' => new ChatbotMessageResource($result['message']),
            'requires_auth' => $result['requires_auth'],
        ], 'Message sent.', 201);
    }

    /**
     * A room belongs to whoever holds its session_id, or the user it has been claimed by.
     * Claims an unclaimed guest room for the caller once they authenticate.
     */
    private function authorizeRoom(Request $request, ChatbotRoom $room): bool
    {
        $sessionId = $request->input('session_id');
        if ($room->session_id !== $sessionId) {
            return false;
        }

        /** @var User|null $user */
        $user = $request->user('sanctum');

        if ($room->user_id) {
            return $user && (int) $user->id === (int) $room->user_id;
        }

        if ($user) {
            $room->update(['user_id' => $user->id]);
        }

        return true;
    }
}
