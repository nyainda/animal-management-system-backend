<?php

namespace App\Enums;

enum AnimalStatus: string
{
    case ACTIVE = 'Active';
    case BUTCHERED = 'Butchered';
    case CULLED = 'Culled';
    case DECEASED = 'Deceased';
    case DRY = 'Dry';
    case FINISHING = 'Finishing';
    case FOR_SALE = 'For Sale';
    case LACTATING = 'Lactating';
    case LOST = 'Lost';
    case OFF_FARM = 'Off Farm';
    case QUARANTINED = 'Quarantined';
    case REFERENCE = 'Reference';
    case SICK = 'Sick';
    case HEALTHY = 'Healthy';
    case SOLD = 'Sold';
    case WEANING = 'Weaning';
    case ARCHIVED = 'Archived';
    case PENDING_APPROVAL = 'Pending Approval';
    case RETIRED = 'Retired';
    case IN_REPRODUCTION = 'In Reproduction';
    case IN_TRANSIT = 'In Transit';
    case AWAITING_ADOPTION = 'Awaiting Adoption';
    case READY_FOR_BREEDING = 'Ready for Breeding';
    case IN_TRAINING = 'In Training';
    case RECOVERING = 'Recovering';
    case ON_DISPLAY = 'On Display';
    case UNDER_EVALUATION = 'Under Evaluation';

    // Define status groups for different actions and workflows
    public function getGroup(): string
    {
        return match($this) {
            self::ACTIVE, self::HEALTHY,
            self::READY_FOR_BREEDING => 'healthy',

            self::SICK, self::RECOVERING,
            self::QUARANTINED => 'medical_concern',

            self::DECEASED, self::CULLED,
            self::BUTCHERED => 'terminal',

            self::FOR_SALE, self::SOLD,
            self::OFF_FARM => 'commercial',

            self::IN_REPRODUCTION,
            self::LACTATING => 'breeding',

            self::WEANING,
            self::IN_TRAINING => 'development',

            default => 'other'
        };
    }

    // Define allowed status transitions
    public function getAllowedTransitions(): array
    {
        return match($this) {
            self::ACTIVE => [
                self::SICK,
                self::QUARANTINED,
                self::FOR_SALE,
                self::IN_REPRODUCTION
            ],

            self::SICK => [
                self::RECOVERING,
                self::QUARANTINED,
                self::DECEASED
            ],

            self::QUARANTINED => [
                self::ACTIVE,
                self::SICK,
                self::DECEASED
            ],

            self::IN_REPRODUCTION => [
                self::LACTATING,
                self::ACTIVE,
                self::RETIRED
            ],

            self::LACTATING => [
                self::DRY,
                self::WEANING,
                self::ACTIVE
            ],

            default => [] // No transitions for most terminal states
        };
    }

    // Business logic for status change
    public static function tryFromCaseInsensitive(string $value): ?self
    {
        $value = strtolower($value);
        foreach (self::cases() as $case) {
            if (strtolower($case->value) === $value) {
                return $case;
            }
        }
        return null;
    }
    public function allowsVaccinations(): bool
    {
        return in_array($this->getGroup(), ['healthy', 'development']);
    }
    public static function normalizeValue(string $value): string
    {
        return self::tryFromCaseInsensitive($value)?->value ?? $value;
    }

    public function canTransitionTo(self $newStatus): bool
{
    // Allow transition to the same status
    if ($this === $newStatus) {
        return true;
    }

    return in_array($newStatus, $this->getAllowedTransitions());
}

    // Automatic actions based on status
    public function requiresAction(): ?string
    {
        return match($this) {
            self::SICK => 'Veterinary check required',
            self::QUARANTINED => 'Isolation and monitoring needed',
            self::RECOVERING => 'Follow-up medical assessment',
            self::IN_REPRODUCTION => 'Breeding monitoring',
            self::WEANING => 'Nutritional plan adjustment',
            self::FOR_SALE => 'Prepare sales documentation',
            self::UNDER_EVALUATION => 'Complete evaluation process',
            default => null
        };
    }

    // Additional metadata for reporting and analytics
    public function getReportingCategory(): string
    {
        return match($this->getGroup()) {
            'healthy' => 'Productive',
            'medical_concern' => 'At Risk',
            'terminal' => 'Removed',
            'commercial' => 'Market',
            'breeding' => 'Reproductive',
            'development' => 'Growth',
            default => 'Other'
        };
    }

    // Color coding for UI
    public function getStatusColor(): string
    {
        return match($this) {
            self::ACTIVE, self::HEALTHY => 'green',
            self::SICK, self::QUARANTINED => 'red',
            self::RECOVERING => 'orange',
            self::IN_REPRODUCTION, self::LACTATING => 'blue',
            self::DECEASED, self::CULLED => 'gray',
            self::FOR_SALE, self::SOLD => 'purple',
            default => 'default'
        };
    }
}
