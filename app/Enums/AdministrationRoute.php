<?php

namespace App\Enums;

enum AdministrationRoute: string
{
    case ORAL = 'oral';
    case INTRAMUSCULAR = 'intramuscular';
    case SUBCUTANEOUS = 'subcutaneous';
    case TOPICAL = 'topical';
    case INTRAVENOUS = 'intravenous';
    case OTHER = 'other';

    // New cases added here
    case INTRAMAMMARY = 'intramammary';
    case INTRAUTERINE = 'intrauterine';
    case INTRADERMAL = 'intradermal';
    case INTRAARTICULAR = 'intraarticular';
    case INTRANASAL = 'intranasal';
    case INTRATHECAL = 'intrathecal';
    case INTRAVESICAL = 'intravesical';
    case INTRAOCULAR = 'intraocular';
    case INTRACARDIAC = 'intracardiac';
    case INTRAPERITONEAL = 'intraperitoneal';
    case INTRATYMPANIC = 'intratympanic';
    case INHALATION = 'inhalation';
    case RECTAL = 'rectal';
    case TRANSDERMAL = 'transdermal';
    case INTRAOSSEOUS = 'intraosseous';
    case SUBLINGUAL = 'sublingual';
    case BUCCAL = 'buccal';
    case INTRAPERICARDIAL = 'intrapericardial';
    case INTRACAVEROUS = 'intracavernous';
    case VAGINAL = 'vaginal';
    case NASOGASTRIC = 'nasogastric';
    case INTRALUMINAL = 'intraluminal';
    case INTRAHEPATIC = 'intrahepatic';
    case EPICUTANEOUS = 'epicutaneous';
    case INTRACEREBRAL = 'intracerebral';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            'oral' => 'Oral (in the mouth)',
            'intramuscular' => 'Intramuscular (in the muscle)',
            'subcutaneous' => 'Subcutaneous (under the skin)',
            'topical' => 'Topical (on the skin)',
            'intravenous' => 'Intravenous (in the vein)',
            'intramammary' => 'Intramammary (in the udder)',
            'intrauterine' => 'Intrauterine (in the uterus)',
            'intradermal' => 'Intradermal (in the skin)',
            'intraarticular' => 'Intraarticular (in the joint)',
            'intranasal' => 'Intranasal (in the nose)',
            'intrathecal' => 'Intrathecal (in the spinal canal)',
            'intravesical' => 'Intravesical (in the bladder)',
            'intraocular' => 'Intraocular (in the eye)',
            'intracardiac' => 'Intracardiac (in the heart)',
            'intraperitoneal' => 'Intraperitoneal (in the abdominal cavity)',
            'intratympanic' => 'Intratympanic (in the ear)',
            'inhalation' => 'Inhalation (through inhaler)',
            'rectal' => 'Rectal (in the rectum)',
            'transdermal' => 'Transdermal (through the skin)',
            'intraosseous' => 'Intraosseous (into the bone)',
            'sublingual' => 'Sublingual (under the tongue)',
            'buccal' => 'Buccal (between the cheek and gum)',
            'intrapericardial' => 'Intrapericardial (in the pericardial sac)',
            'intracavernous' => 'Intracavernous (in the penis)',
            'vaginal' => 'Vaginal (in the vagina)',
            'nasogastric' => 'Nasogastric (through the nose into the stomach)',
            'intraluminal' => 'Intraluminal (in the lumen of a tubular organ)',
            'intrahepatic' => 'Intrahepatic (in the liver)',
            'epicutaneous' => 'Epicutaneous (on the skin, e.g., patch)',
            'intracerebral' => 'Intracerebral (in the brain)',
            'other' => 'Other',
        ];
    }
}
