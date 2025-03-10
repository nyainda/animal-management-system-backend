<?php

namespace App\Enums;

enum TransactionType: string {
    case Sale = 'sale';
    case Purchase = 'purchase';
    case Lease = 'lease';
    case Transfer = 'transfer';
    case Donation = 'donation';
    case Exchange = 'exchange';
    case BreedingFee = 'breeding_fee';
}
