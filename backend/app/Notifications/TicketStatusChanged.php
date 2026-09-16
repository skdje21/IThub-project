<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Ticket $ticket,
        private readonly TicketStatus $oldStatus,
        private readonly TicketStatus $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Ticket #{$this->ticket->id} status changed")
            ->line("Your ticket \"{$this->ticket->title}\" status has been changed.")
            ->line("From: {$this->oldStatus->value} → To: {$this->newStatus->value}")
            ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
    }
}
