<?php

namespace App\Models\Concerns;

use App\Enums\AnimalStatus;

trait HasStatus
{
    public function canChangeStatusTo(AnimalStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function getStatusAction(): ?string
    {
        return $this->status->requiresAction();
    }

    public function getStatusReportingCategory(): string
    {
        return $this->status->getReportingCategory();
    }

    public function getStatusColor(): string
    {
        return $this->status->getStatusColor();
    }
}
