<?php

namespace App\Enums;

enum TaskType: string
{
    case FEEDING = 'feeding';
    case VACCINATION = 'vaccination';
    case MILKING = 'milking';
    case CLEANING = 'cleaning';
    case HEALTH_CHECK = 'health_check';
}
