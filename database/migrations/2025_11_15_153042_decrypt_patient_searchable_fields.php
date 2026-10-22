<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration decrypts searchable patient fields for performance.
     * Fields decrypted: name, barangay, contact_no, date_of_birth
     * Fields kept encrypted: mother_name, father_name, address, place_of_birth, birth_height, birth_weight
     */
    public function up(): void
    {
        // Step 1: Decrypt data while encryption is still active in model
        $this->info('Decrypting patient searchable fields...');
        
        // Temporarily disable model events to prevent issues
        Patient::unguard();
        
        foreach (Patient::all() as $patient) {
            // Read encrypted data (Laravel auto-decrypts)
            $decryptedData = [
                'name' => $patient->name,
                'barangay' => $patient->barangay,
                'contact_no' => $patient->contact_no,
                'date_of_birth' => $patient->date_of_birth,
            ];
            
            // Update directly in database as plain text using DB facade
            DB::table('patients')
                ->where('id', $patient->id)
                ->update($decryptedData);
        }
        
        Patient::reguard();
        
        $this->info('Decryption complete. Updating column types...');
        
        // Step 2: Change column types from TEXT to VARCHAR for better performance
        Schema::table('patients', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('barangay', 100)->change();
            $table->string('contact_no', 50)->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
        });
        
        $this->info('Column types updated successfully.');
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: This will re-encrypt the data. Only run if you need to rollback.
     */
    public function down(): void
    {
        $this->info('Rolling back: Re-encrypting patient fields...');
        
        // Step 1: Change columns back to TEXT
        Schema::table('patients', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('barangay')->change();
            $table->text('contact_no')->nullable()->change();
            $table->text('date_of_birth')->nullable()->change();
        });
        
        // Step 2: Re-encrypt data (only if encryption is re-enabled in model)
        $this->warn('Note: You need to re-enable encryption in Patient model before running down()');
        $this->warn('Otherwise, data will remain as plain text in TEXT columns.');
    }
    
    /**
     * Helper method to output info during migration
     */
    private function info($message)
    {
        echo "[INFO] " . $message . PHP_EOL;
    }
    
    /**
     * Helper method to output warnings during migration
     */
    private function warn($message)
    {
        echo "[WARN] " . $message . PHP_EOL;
    }
};
