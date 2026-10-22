<?php

namespace App\Console\Commands;

use App\Models\Parents;
use App\Models\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteParentsExceptProtected extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parents:delete-except-protected
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all parent accounts and their associated patients except parent ID 47';

    /**
     * Protected parent IDs that should never be deleted
     *
     * @var array
     */
    protected $protectedParentIds = [47];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║   DELETE PARENTS & PATIENTS (EXCEPT PROTECTED)             ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Get counts before deletion
        $totalParents = Parents::count();
        $protectedParents = Parents::whereIn('id', $this->protectedParentIds)->count();
        $parentsToDelete = Parents::whereNotIn('id', $this->protectedParentIds)->count();
        
        $totalPatients = Patient::count();
        $protectedPatients = Patient::whereIn('parent_id', $this->protectedParentIds)->count();
        $patientsToDelete = Patient::whereNotIn('parent_id', $this->protectedParentIds)->count();

        // Show summary
        $this->table(
            ['Category', 'Total', 'Protected', 'To Delete'],
            [
                ['Parents', $totalParents, $protectedParents, $parentsToDelete],
                ['Patients', $totalPatients, $protectedPatients, $patientsToDelete],
            ]
        );

        $this->newLine();
        $this->warn('⚠️  PROTECTED PARENT IDS: ' . implode(', ', $this->protectedParentIds));
        $this->newLine();

        // Show what will be deleted
        if ($parentsToDelete > 0) {
            $this->info('📋 Related data that will also be deleted:');
            $this->line('   • Notifications (push notifications, in-app)');
            $this->line('   • SMS logs');
            $this->line('   • Push subscriptions');
            $this->line('   • Feedback records');
            $this->line('   • Patient vaccine records');
            $this->line('   • Patient growth records');
            $this->line('   • Vaccination transactions');
            $this->newLine();
        }

        if ($parentsToDelete === 0) {
            $this->info('✅ No parents to delete. All existing parents are protected.');
            return Command::SUCCESS;
        }

        // Confirm deletion
        if (!$this->option('force')) {
            $this->error("🚨 WARNING: This action will permanently delete {$parentsToDelete} parent(s) and {$patientsToDelete} patient(s)!");
            $this->newLine();
            
            if (!$this->confirm('Are you absolutely sure you want to proceed?')) {
                $this->info('❌ Operation cancelled.');
                return Command::FAILURE;
            }

            // Double confirmation for safety
            $this->newLine();
            if (!$this->confirm('⚠️  FINAL CONFIRMATION: Delete all data except parent ID 47?', false)) {
                $this->info('❌ Operation cancelled.');
                return Command::FAILURE;
            }
        }

        $this->newLine();
        $this->info('🔄 Starting deletion process...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // Get patient IDs to delete (for related records)
            $patientIdsToDelete = Patient::whereNotIn('parent_id', $this->protectedParentIds)
                ->pluck('id')
                ->toArray();

            // Get parent IDs to delete (for related records)
            $parentIdsToDelete = Parents::whereNotIn('id', $this->protectedParentIds)
                ->pluck('id')
                ->toArray();

            $deletedCounts = [
                'vaccine_records' => 0,
                'growth_records' => 0,
                'vaccination_transactions' => 0,
                'notifications' => 0,
                'sms_logs' => 0,
                'push_subscriptions' => 0,
                'feedbacks' => 0,
                'patients' => 0,
                'parents' => 0,
            ];

            // Step 1: Delete patient-related records
            if (!empty($patientIdsToDelete)) {
                $this->info('🗑️  Deleting patient-related records...');
                
                // Delete patient vaccine records
                $deletedCounts['vaccine_records'] = DB::table('patient_vaccine_records')
                    ->whereIn('patient_id', $patientIdsToDelete)
                    ->delete();
                $this->line("   ✓ Deleted {$deletedCounts['vaccine_records']} vaccine records");

                // Delete patient growth records
                $deletedCounts['growth_records'] = DB::table('patient_growth_records')
                    ->whereIn('patient_id', $patientIdsToDelete)
                    ->delete();
                $this->line("   ✓ Deleted {$deletedCounts['growth_records']} growth records");

                // Delete vaccination transactions
                $deletedCounts['vaccination_transactions'] = DB::table('vaccination_transactions')
                    ->whereIn('patient_id', $patientIdsToDelete)
                    ->delete();
                $this->line("   ✓ Deleted {$deletedCounts['vaccination_transactions']} vaccination transactions");
            }

            // Step 2: Delete parent-related records
            if (!empty($parentIdsToDelete)) {
                $this->newLine();
                $this->info('🗑️  Deleting parent-related records...');

                // Delete notifications (polymorphic - Parents model)
                $deletedCounts['notifications'] = DB::table('notifications')
                    ->where('notifiable_type', 'App\\Models\\Parents')
                    ->whereIn('notifiable_id', $parentIdsToDelete)
                    ->delete();
                $this->line("   ✓ Deleted {$deletedCounts['notifications']} notifications");

                // Delete SMS logs (polymorphic - Parents model)
                $deletedCounts['sms_logs'] = DB::table('sms_logs')
                    ->where('notifiable_type', 'App\\Models\\Parents')
                    ->whereIn('notifiable_id', $parentIdsToDelete)
                    ->delete();
                $this->line("   ✓ Deleted {$deletedCounts['sms_logs']} SMS logs");

                // Delete push subscriptions (polymorphic - Parents model)
                $deletedCounts['push_subscriptions'] = DB::table('push_subscriptions')
                    ->where('subscribable_type', 'App\\Models\\Parents')
                    ->whereIn('subscribable_id', $parentIdsToDelete)
                    ->delete();
                $this->line("   ✓ Deleted {$deletedCounts['push_subscriptions']} push subscriptions");

                // Delete feedbacks (if table exists)
                if (DB::getSchemaBuilder()->hasTable('feedbacks')) {
                    $deletedCounts['feedbacks'] = DB::table('feedbacks')
                        ->whereIn('parent_id', $parentIdsToDelete)
                        ->delete();
                    $this->line("   ✓ Deleted {$deletedCounts['feedbacks']} feedbacks");
                } else {
                    $this->line("   ⊘ Feedbacks table not found (skipped)");
                }
            }

            // Step 3: Delete patients
            $this->newLine();
            $this->info('🗑️  Deleting patients...');
            $deletedCounts['patients'] = Patient::whereNotIn('parent_id', $this->protectedParentIds)->delete();
            $this->line("   ✓ Deleted {$deletedCounts['patients']} patients");

            // Step 4: Delete parents
            $this->newLine();
            $this->info('🗑️  Deleting parent accounts...');
            $deletedCounts['parents'] = Parents::whereNotIn('id', $this->protectedParentIds)->delete();
            $this->line("   ✓ Deleted {$deletedCounts['parents']} parent accounts");

            DB::commit();

            // Success summary
            $this->newLine();
            $this->info('╔════════════════════════════════════════════════════════════╗');
            $this->info('║   DELETION COMPLETED SUCCESSFULLY                          ║');
            $this->info('╚════════════════════════════════════════════════════════════╝');
            $this->newLine();

            $this->table(
                ['Record Type', 'Deleted Count'],
                [
                    ['Parent Accounts', $deletedCounts['parents']],
                    ['Patients', $deletedCounts['patients']],
                    ['Vaccine Records', $deletedCounts['vaccine_records']],
                    ['Growth Records', $deletedCounts['growth_records']],
                    ['Vaccination Transactions', $deletedCounts['vaccination_transactions']],
                    ['Notifications', $deletedCounts['notifications']],
                    ['SMS Logs', $deletedCounts['sms_logs']],
                    ['Push Subscriptions', $deletedCounts['push_subscriptions']],
                    ['Feedbacks', $deletedCounts['feedbacks']],
                ]
            );

            // Verify protected data is intact
            $this->newLine();
            $this->info('🔒 Verifying protected data...');
            $remainingParents = Parents::whereIn('id', $this->protectedParentIds)->count();
            $remainingPatients = Patient::whereIn('parent_id', $this->protectedParentIds)->count();
            
            $this->line("   ✓ Protected parents remaining: {$remainingParents}");
            $this->line("   ✓ Protected patients remaining: {$remainingPatients}");

            if ($remainingParents === $protectedParents && $remainingPatients === $protectedPatients) {
                $this->newLine();
                $this->info('✅ All protected data is intact!');
            } else {
                $this->newLine();
                $this->warn('⚠️  Warning: Protected data counts changed. Please verify manually.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->newLine();
            $this->error('❌ ERROR: Deletion failed!');
            $this->error('Message: ' . $e->getMessage());
            $this->newLine();
            $this->info('🔄 All changes have been rolled back. No data was deleted.');
            
            return Command::FAILURE;
        }
    }
}
