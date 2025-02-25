<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case CHECK = 'check';
    case CRYPTO = 'crypto';
    case PAYMENT_PLAN = 'payment_plan';
}
