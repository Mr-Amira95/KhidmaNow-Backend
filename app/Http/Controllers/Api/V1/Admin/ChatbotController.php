<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatbotMessageResource;
use App\Http\Resources\ChatbotRoomResource;
use App\Http\Traits\ApiResponse;
use App\Models\ChatbotRoom;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = ChatbotRoom::query()
            ->with(['user', 'latestMessage'])
            ->when($request->filled('direction'), fn ($q) => $q->where('direction', $request->direction))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('session_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('updated_at');

        return $this->paginated(ChatbotRoomResource::class, $query);
    }

    public function messages(Request $request, ChatbotRoom $chatbotRoom)
    {
        $query = $chatbotRoom->messages()->with(['suggestions.provider.user', 'suggestions.provider.city', 'quotation'])->latest();

        return $this->paginated(ChatbotMessageResource::class, $query);
    }
}
