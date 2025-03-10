<?php

namespace App\Enums;

enum TransactionStatus: string {
    case Pending = 'pending';
    case DepositPaid = 'deposit_paid';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Disputed = 'disputed';
}
