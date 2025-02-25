<?php

namespace App\Enums;

enum TransactionType: string
{
    case SALE = 'sale';
    case PURCHASE = 'purchase';
    case LEASE = 'lease';
    case TRANSFER = 'transfer';
    case DONATION = 'donation';
    case EXCHANGE = 'exchange';
    case BREEDING_FEE = 'breeding_fee';
}
