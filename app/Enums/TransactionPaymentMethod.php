<?php

namespace App\Enums;

enum TransactionPaymentMethod: string {
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case CreditCard = 'credit_card';
    case Check = 'check';
    case Crypto = 'crypto';
    case PaymentPlan = 'payment_plan';
}
