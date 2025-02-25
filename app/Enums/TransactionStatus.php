<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case DEPOSIT_PAID = 'deposit_paid';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case DISPUTED = 'disputed';
}
