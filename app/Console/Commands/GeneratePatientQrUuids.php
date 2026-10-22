<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GeneratePatientQrUuids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:generate-qr-uuids 
                            {--force : Regenerate UUIDs for ALL patients, even those with existing UUIDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR UUIDs for patients that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        
        if ($force) {
            if (!$this->confirm('This will regenerate QR UUIDs for ALL patients. Existing QR codes will no longer work. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
            $patients = Patient::all();
            $this->info("Regenerating QR UUIDs for all {$patients->count()} patients...");
        } else {
            $patients = Patient::whereNull('qr_uuid')->orWhere('qr_uuid', '')->get();
            
            if ($patients->isEmpty()) {
                $this->info('All patients already have QR UUIDs. Nothing to do.');
                $this->newLine();
                $this->table(
                    ['Total Patients', 'With QR UUID'],
                    [[Patient::count(), Patient::whereNotNull('qr_uuid')->where('qr_uuid', '!=', '')->count()]]
                );
                return 0;
            }
            
            $this->info("Found {$patients->count()} patients without QR UUIDs.");
        }
        
        $bar = $this->output->createProgressBar($patients->count());
        $bar->start();
        
        $updated = 0;
        foreach ($patients as $patient) {
            $patient->qr_uuid = Str::uuid()->toString();
            $patient->save();
            $updated++;
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Successfully generated QR UUIDs for {$updated} patients.");
        $this->newLine();
        
        // Show summary
        $this->table(
            ['Total Patients', 'With QR UUID', 'Without QR UUID'],
            [[
                Patient::count(),
                Patient::whereNotNull('qr_uuid')->where('qr_uuid', '!=', '')->count(),
                Patient::whereNull('qr_uuid')->orWhere('qr_uuid', '')->count()
            ]]
        );
        
        return 0;
    }
}
