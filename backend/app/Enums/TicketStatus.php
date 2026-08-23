<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Rejected = 'rejected';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Pending],
            self::Pending => [self::Approved, self::Rejected],
            self::Approved => [self::InProgress, self::Rejected],
            self::InProgress => [self::Done],
            self::Done => [],
            self::Rejected => [self::Pending],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
