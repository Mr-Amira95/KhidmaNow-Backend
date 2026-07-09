<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportTicketReplyRequest;
use App\Http\Resources\SupportTicketReplyResource;
use App\Http\Resources\SupportTicketResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function index(Request $request)
    {
        $query = SupportTicket::query()
            ->with('user')
            ->withCount('replies')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();

        return $this->paginated(SupportTicketResource::class, $query);
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load('user', 'closedBy');

        return $this->success(new SupportTicketResource($supportTicket));
    }

    public function replies(Request $request, SupportTicket $supportTicket)
    {
        $query = $supportTicket->replies()->with('sender')->latest();

        return $this->paginated(SupportTicketReplyResource::class, $query);
    }

    public function sendReply(StoreSupportTicketReplyRequest $request, SupportTicket $supportTicket)
    {
        if ($supportTicket->status === 'closed') {
            return $this->error('This ticket is closed. Reopen it to add a reply.', 422);
        }

        $data = [
            'ticket_id' => $supportTicket->id,
            'sender_id' => $request->user()->id,
            'message' => $request->validated('message'),
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_type'] = $this->attachmentType($file);
            $data['attachment_url'] = $this->storeUpload($file, 'support-tickets');
        }

        $reply = $supportTicket->replies()->create($data);
        $reply->load('sender');

        return $this->success(new SupportTicketReplyResource($reply), 'Reply sent.', 201);
    }

    public function close(Request $request, SupportTicket $supportTicket)
    {
        if ($supportTicket->status === 'closed') {
            return $this->error('This ticket is already closed.', 422);
        }

        $supportTicket->update([
            'status' => 'closed',
            'closed_by' => $request->user()->id,
            'closed_at' => now(),
        ]);

        return $this->success(new SupportTicketResource($supportTicket->load('user', 'closedBy')), 'Support ticket closed.');
    }

    public function reopen(Request $request, SupportTicket $supportTicket)
    {
        if ($supportTicket->status === 'open') {
            return $this->error('This ticket is already open.', 422);
        }

        $supportTicket->update([
            'status' => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);

        return $this->success(new SupportTicketResource($supportTicket->load('user')), 'Support ticket reopened.');
    }
}
