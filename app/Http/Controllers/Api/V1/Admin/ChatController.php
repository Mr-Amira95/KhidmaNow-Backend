<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatRoomResource;
use App\Http\Resources\MessageResource;
use App\Http\Traits\ApiResponse;
use App\Models\ChatRoom;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = ChatRoom::query()
            ->with(['user', 'provider.user', 'latestMessage'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                    })->orWhereHas('provider', function ($q) use ($search) {
                        $q->where('business_name', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
                });
            })
            ->latest('last_message_at');

        return $this->paginated(ChatRoomResource::class, $query);
    }

    public function messages(Request $request, ChatRoom $chatRoom)
    {
        $query = $chatRoom->messages()->with('sender')->latest();

        return $this->paginated(MessageResource::class, $query);
    }
}
