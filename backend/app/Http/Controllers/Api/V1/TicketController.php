<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeTicketStatusRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $tickets = Ticket::query()
            ->with(['category', 'author', 'assignee', 'department'])
            ->withCount('comments')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->priority, fn ($q, $v) => $q->where('priority', $v))
            ->when($request->assignee_id, fn ($q, $v) => $q->where('assignee_id', $v))
            ->latest()
            ->paginate(15);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
            'department_id' => $request->user()->department_id,
            'status' => TicketStatus::Draft,
        ]);

        return (new TicketResource($ticket->load(['category', 'author', 'department'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ticket $ticket): TicketResource
    {
        return new TicketResource(
            $ticket->load(['category', 'author', 'assignee', 'department'])
                ->loadCount('comments')
        );
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): TicketResource
    {
        $ticket->update($request->validated());

        return new TicketResource($ticket->load(['category', 'author', 'assignee', 'department']));
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->json(null, 204);
    }

    public function changeStatus(ChangeTicketStatusRequest $request, Ticket $ticket): TicketResource
    {
        $ticket = $this->ticketService->changeStatus(
            $ticket,
            TicketStatus::from($request->validated('status')),
            $request->user(),
        );

        return new TicketResource($ticket->load(['category', 'author', 'assignee', 'department']));
    }
}
