<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusChanged;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function changeStatus(Ticket $ticket, TicketStatus $newStatus, User $user): Ticket
    {
        $currentStatus = $ticket->status;

        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from {$currentStatus->value} to {$newStatus->value}.",
            ]);
        }

        $ticket->status = $newStatus;

        if ($newStatus === TicketStatus::Done) {
            $ticket->resolved_at = now();
        }

        $ticket->save();

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'status_changed',
            'changes' => [
                'from' => $currentStatus->value,
                'to' => $newStatus->value,
            ],
        ]);

        $ticket->author->notify(new TicketStatusChanged($ticket, $currentStatus, $newStatus));

        return $ticket;
    }
}
