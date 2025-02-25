<?php

namespace App\Enums;

enum TreatmentType: string
{
    // Medical Procedures
    case MEDICATION = 'medication';
    case VACCINATION = 'vaccination';
    case SURGERY = 'surgical_procedure';
    case EMERGENCY_CARE = 'emergency_care';
    case WOUND_CARE = 'wound_care';
    case DENTAL_PROCEDURE = 'dental_procedure';
    case ENDOSCOPY = 'endoscopy';

    // Preventive Care
    case DEWORMING = 'deworming';
    case PARASITE_TREATMENT = 'parasite_treatment';
    case FLY_TREATMENT = 'fly_treatment';
    case MITES_TREATMENT = 'mites';

    // Identification & Physical Alterations
    case BRANDING = 'branding';
    case TAGGING = 'tagging';
    case TATTOO = 'tattoo';
    case EAR_NOTCHING = 'ear_notching';
    case DEHORNING = 'dehorning';
    case CASTRATION = 'castration';

    // Maintenance
    case HOOF_TRIM = 'hoof_trim';
    case GROOMING = 'grooming';

    // Reproductive Services
    case ARTIFICIAL_INSEMINATION = 'artificial_insemination';
    case BREEDING_ASSISTANCE = 'breeding_assistance';
    case REPRODUCTIVE_SERVICES = 'reproductive_services';

    // Diagnostic & Consultative
    case DIAGNOSTICS = 'diagnostics';
    case GENETIC_TESTING = 'genetic_testing';
    case NUTRITIONAL_CONSULTATION = 'nutritional_consultation';

    // Alternative & Behavioral
    case ALTERNATIVE_THERAPY = 'alternative_therapy';
    case BEHAVIORAL_TRAINING = 'behavioral_training';

    // End of Life
    case EUTHANASIA = 'euthanasia';

    /**
     * Get all enum values as an array
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable name for the enum value
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::MEDICATION => 'Medication',
            self::VACCINATION => 'Vaccination',
            self::SURGERY => 'Surgical Procedure',
            self::EMERGENCY_CARE => 'Emergency Care',
            self::WOUND_CARE => 'Wound Care',
            self::DENTAL_PROCEDURE => 'Dental Procedure',
            self::ENDOSCOPY => 'Endoscopy',
            self::DEWORMING => 'Deworming',
            self::PARASITE_TREATMENT => 'Parasite Treatment',
            self::FLY_TREATMENT => 'Fly Treatment',
            self::MITES_TREATMENT => 'Mites',
            self::BRANDING => 'Branding',
            self::TAGGING => 'Tagging',
            self::TATTOO => 'Tattoo',
            self::EAR_NOTCHING => 'Ear Notching',
            self::DEHORNING => 'Dehorning',
            self::CASTRATION => 'Castration',
            self::HOOF_TRIM => 'Hoof Trim',
            self::GROOMING => 'Grooming',
            self::ARTIFICIAL_INSEMINATION => 'Artificial Insemination',
            self::BREEDING_ASSISTANCE => 'Breeding Assistance',
            self::REPRODUCTIVE_SERVICES => 'Reproductive Services',
            self::DIAGNOSTICS => 'Diagnostics',
            self::GENETIC_TESTING => 'Genetic Testing',
            self::NUTRITIONAL_CONSULTATION => 'Nutritional Consultation',
            self::ALTERNATIVE_THERAPY => 'Alternative Therapy',
            self::BEHAVIORAL_TRAINING => 'Behavioral Training',
            self::EUTHANASIA => 'Euthanasia',
        };
    }
}
