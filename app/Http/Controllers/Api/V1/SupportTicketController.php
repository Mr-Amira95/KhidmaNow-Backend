<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportTicketReplyRequest;
use App\Http\Requests\StoreSupportTicketRequest;
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
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->withCount('replies')
            ->with('latestReply')
            ->latest();

        return $this->paginated(SupportTicketResource::class, $query);
    }

    public function store(StoreSupportTicketRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_type'] = $this->attachmentType($file);
            $data['attachment_url'] = $this->storeUpload($file, 'support-tickets');
        }

        $ticket = SupportTicket::create($data)->refresh();
        $ticket->load('user');

        return $this->success(new SupportTicketResource($ticket), 'Support ticket opened.', 201);
    }

    public function close(Request $request, SupportTicket $supportTicket)
    {
        $user = $request->user();
        if (!$supportTicket->isParticipant($user)) {
            return $this->error('You are not allowed to close this ticket.', 403);
        }

        if ($supportTicket->status === 'closed') {
            return $this->error('This ticket is already closed.', 422);
        }

        $supportTicket->update([
            'status' => 'closed',
            'closed_by' => $user->id,
            'closed_at' => now(),
        ]);

        return $this->success(new SupportTicketResource($supportTicket->load('user', 'closedBy')), 'Support ticket closed.');
    }

    public function reopen(Request $request, SupportTicket $supportTicket)
    {
        $user = $request->user();
        if (!$supportTicket->isParticipant($user)) {
            return $this->error('You are not allowed to reopen this ticket.', 403);
        }

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

    public function replies(Request $request, SupportTicket $supportTicket)
    {
        if (!$supportTicket->isParticipant($request->user())) {
            return $this->error('You are not allowed to view this ticket.', 403);
        }

        $query = $supportTicket->replies()->with('sender')->latest();

        return $this->paginated(SupportTicketReplyResource::class, $query);
    }

    public function sendReply(StoreSupportTicketReplyRequest $request, SupportTicket $supportTicket)
    {
        $user = $request->user();
        if (!$supportTicket->isParticipant($user)) {
            return $this->error('You are not allowed to reply to this ticket.', 403);
        }

        if ($supportTicket->status === 'closed') {
            return $this->error('This ticket is closed. Reopen it to add a reply.', 422);
        }

        $data = [
            'ticket_id' => $supportTicket->id,
            'sender_id' => $user->id,
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
}
