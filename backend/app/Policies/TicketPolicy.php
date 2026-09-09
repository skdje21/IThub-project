<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->author_id || $user->isAdmin();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->author_id || $user->isAdmin();
    }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->id === $ticket->assignee_id;
    }
}
