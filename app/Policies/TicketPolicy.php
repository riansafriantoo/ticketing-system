<?php

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
        return $user->isAgent() || $ticket->requester_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isAgent();
    }

    public function transition(User $user, Ticket $ticket): bool
    {
        // Agents can do all transitions; requesters can only close their own
        if ($user->isAgent()) return true;

        return $ticket->requester_id === $user->id;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isAgent();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }
}