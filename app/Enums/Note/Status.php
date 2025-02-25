<?php

namespace App\Enums\Note;

enum Status: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';
}
