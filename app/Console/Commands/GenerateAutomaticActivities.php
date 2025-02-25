<?php
// app/Console/Commands/GenerateAutomaticActivities.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnimalActivityService;

class GenerateAutomaticActivities extends Command
{
    protected $signature = 'animal-activities:generate';
    protected $description = 'Generate automatic animal activities';

    public function handle(AnimalActivityService $service)
    {
        $this->info('Generating birthday activities...');
        $service->generateBirthdayActivities();
        $this->info('Birthday activities generated successfully!');
    }
}
