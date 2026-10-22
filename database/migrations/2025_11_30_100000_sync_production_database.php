<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Comprehensive Database Sync Migration
 * 
 * This migration adds all missing columns, indexes, and foreign keys
 * to sync production database with local development database.
 * 
 * All operations check if the column/index exists before adding to prevent errors.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // BARANGAYS TABLE
        // ============================================
        if (!Schema::hasTable('barangays')) {
            Schema::create('barangays', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('has_scheduled_vaccination')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['is_active', 'has_scheduled_vaccination'], 'idx_barangays_active_schedulable');
                $table->index('name', 'idx_barangays_name');
            });
        }

        // ============================================
        // PARENTS TABLE - Add missing columns
        // ============================================
        Schema::table('parents', function (Blueprint $table) {
            if (!Schema::hasColumn('parents', 'email')) {
                $table->string('email')->nullable()->after('username');
            }
            if (!Schema::hasColumn('parents', 'barangay')) {
                $table->string('barangay')->default('')->after('email');
            }
            if (!Schema::hasColumn('parents', 'address')) {
                $table->text('address')->nullable()->after('barangay');
            }
            if (!Schema::hasColumn('parents', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('address');
            }
            if (!Schema::hasColumn('parents', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('contact_number');
            }
            if (!Schema::hasColumn('parents', 'password_changed')) {
                $table->boolean('password_changed')->default(true)->after('password');
            }
            if (!Schema::hasColumn('parents', 'privacy_policy_accepted')) {
                $table->boolean('privacy_policy_accepted')->default(false)->after('password_changed');
            }
            if (!Schema::hasColumn('parents', 'privacy_policy_accepted_at')) {
                $table->timestamp('privacy_policy_accepted_at')->nullable()->after('privacy_policy_accepted');
            }
            if (!Schema::hasColumn('parents', 'privacy_policy_version')) {
                $table->string('privacy_policy_version', 10)->default('1.0')->after('privacy_policy_accepted_at');
            }
        });

        // Add indexes to parents table
        $this->addIndexIfNotExists('parents', 'idx_parents_barangay', ['barangay']);

        // ============================================
        // HEALTH_WORKERS TABLE - Add missing columns
        // ============================================
        Schema::table('health_workers', function (Blueprint $table) {
            if (!Schema::hasColumn('health_workers', 'barangay_id')) {
                $table->unsignedBigInteger('barangay_id')->nullable()->after('email');
            }
            if (!Schema::hasColumn('health_workers', 'password_reset_token')) {
                $table->string('password_reset_token')->nullable()->after('barangay_id');
            }
            if (!Schema::hasColumn('health_workers', 'password_reset_requested_at')) {
                $table->timestamp('password_reset_requested_at')->nullable()->after('password_reset_token');
            }
        });

        // Add foreign key for barangay_id
        $this->addForeignKeyIfNotExists(
            'health_workers',
            'health_workers_barangay_id_foreign',
            'barangay_id',
            'barangays',
            'id'
        );

        // ============================================
        // PATIENTS TABLE - Add missing columns and indexes
        // ============================================
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'barangay')) {
                $table->string('barangay', 100)->default('')->after('updated_at');
            }
            if (!Schema::hasColumn('patients', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('sex');
            }
        });

        // Add indexes to patients table
        $this->addIndexIfNotExists('patients', 'idx_patients_barangay', ['barangay']);
        $this->addIndexIfNotExists('patients', 'idx_patients_barangay_dob', ['barangay', 'date_of_birth']);
        $this->addIndexIfNotExists('patients', 'idx_patients_contact_no', ['contact_no']);
        $this->addIndexIfNotExists('patients', 'idx_patients_dob', ['date_of_birth']);
        $this->addIndexIfNotExists('patients', 'idx_patients_created_at', ['created_at']);
        $this->addIndexIfNotExists('patients', 'idx_patients_name', ['name']);

        // Add foreign key for parent_id
        $this->addForeignKeyIfNotExists(
            'patients',
            'fk_parent_id',
            'parent_id',
            'parents',
            'id'
        );

        // ============================================
        // VACCINES TABLE - Add missing columns
        // ============================================
        Schema::table('vaccines', function (Blueprint $table) {
            if (!Schema::hasColumn('vaccines', 'stocks')) {
                $table->integer('stocks')->default(0)->after('doses_description');
            }
            if (!Schema::hasColumn('vaccines', 'available_bottles')) {
                $table->integer('available_bottles')->default(0)->after('stocks');
            }
            if (!Schema::hasColumn('vaccines', 'doses_per_bottle')) {
                $table->integer('doses_per_bottle')->default(10)->after('available_bottles');
            }
            if (!Schema::hasColumn('vaccines', 'doses_used_from_current_bottles')) {
                $table->integer('doses_used_from_current_bottles')->default(0)->after('doses_per_bottle');
            }
        });

        // Add index to vaccines table
        $this->addIndexIfNotExists('vaccines', 'idx_vaccines_name', ['vaccine_name']);

        // ============================================
        // PATIENT_VACCINE_RECORDS TABLE - Add indexes
        // ============================================
        $this->addIndexIfNotExists('patient_vaccine_records', 'idx_pvr_vaccine', ['vaccine_id']);
        $this->addIndexIfNotExists('patient_vaccine_records', 'idx_pvr_vaccine_dose1', ['vaccine_id', 'dose_1_date']);
        $this->addIndexIfNotExists('patient_vaccine_records', 'idx_pvr_vaccine_dose2', ['vaccine_id', 'dose_2_date']);
        $this->addIndexIfNotExists('patient_vaccine_records', 'idx_pvr_vaccine_dose3', ['vaccine_id', 'dose_3_date']);
        $this->addIndexIfNotExists('patient_vaccine_records', 'idx_pvr_patient_dose1', ['patient_id', 'dose_1_date']);

        // Add unique constraint if not exists
        $this->addUniqueIfNotExists('patient_vaccine_records', 'unique_patient_vaccine', ['patient_id', 'vaccine_id']);

        // ============================================
        // FEEDBACK TABLE - Add missing columns and indexes
        // ============================================
        if (Schema::hasTable('feedback')) {
            Schema::table('feedback', function (Blueprint $table) {
                if (!Schema::hasColumn('feedback', 'parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('feedback', 'vaccination_schedule_id')) {
                    $table->unsignedBigInteger('vaccination_schedule_id')->nullable()->after('parent_id');
                }
                if (!Schema::hasColumn('feedback', 'barangay')) {
                    $table->string('barangay')->nullable()->after('vaccination_schedule_id');
                }
                if (!Schema::hasColumn('feedback', 'patient_id')) {
                    $table->unsignedBigInteger('patient_id')->nullable()->after('barangay');
                }
            });

            $this->addIndexIfNotExists('feedback', 'idx_feedback_barangay', ['barangay']);
        }

        // ============================================
        // VACCINATION_SCHEDULES TABLE - Add missing columns
        // ============================================
        if (Schema::hasTable('vaccination_schedules')) {
            Schema::table('vaccination_schedules', function (Blueprint $table) {
                if (!Schema::hasColumn('vaccination_schedules', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('vaccination_schedules', 'cancellation_reason')) {
                    $table->text('cancellation_reason')->nullable()->after('cancelled_at');
                }
                if (!Schema::hasColumn('vaccination_schedules', 'cancelled_by')) {
                    $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancellation_reason');
                }
            });

            $this->addIndexIfNotExists('vaccination_schedules', 'idx_vacc_schedules_barangay', ['barangay']);
            $this->addIndexIfNotExists('vaccination_schedules', 'idx_vacc_schedules_date_status', ['vaccination_date', 'status']);
        }

        // ============================================
        // VACCINATION_TRANSACTIONS TABLE - Add missing columns
        // ============================================
        if (Schema::hasTable('vaccination_transactions')) {
            Schema::table('vaccination_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('vaccination_transactions', 'vaccination_session_id')) {
                    $table->string('vaccination_session_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('vaccination_transactions', 'vaccination_schedule_id')) {
                    $table->unsignedBigInteger('vaccination_schedule_id')->nullable()->after('vaccination_session_id');
                }
                if (!Schema::hasColumn('vaccination_transactions', 'administered_elsewhere')) {
                    $table->boolean('administered_elsewhere')->default(false)->after('vaccination_schedule_id');
                }
                if (!Schema::hasColumn('vaccination_transactions', 'stock_override')) {
                    $table->boolean('stock_override')->default(false)->after('administered_elsewhere');
                }
            });
        }

        // ============================================
        // VACCINATION_REPORT_SNAPSHOTS TABLE - Add missing columns
        // ============================================
        if (Schema::hasTable('vaccination_report_snapshots')) {
            Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
                if (!Schema::hasColumn('vaccination_report_snapshots', 'month_start')) {
                    $table->tinyInteger('month_start')->nullable()->after('quarter_start');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'month_end')) {
                    $table->tinyInteger('month_end')->nullable()->after('quarter_end');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'version')) {
                    $table->integer('version')->default(1)->after('vaccine_name');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'eligible_population_under_1_year')) {
                    $table->integer('eligible_population_under_1_year')->default(0)->after('eligible_population');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'eligible_population_0_12_months')) {
                    $table->integer('eligible_population_0_12_months')->default(0)->after('eligible_population_under_1_year');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'eligible_population_13_23_months')) {
                    $table->integer('eligible_population_13_23_months')->default(0)->after('eligible_population_0_12_months');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'saved_at')) {
                    $table->timestamp('saved_at')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'saved_by')) {
                    $table->unsignedBigInteger('saved_by')->nullable()->after('saved_at');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable()->after('saved_by');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'deleted_by')) {
                    $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                }
                if (!Schema::hasColumn('vaccination_report_snapshots', 'deletion_reason')) {
                    $table->text('deletion_reason')->nullable()->after('deleted_by');
                }
            });
        }

        // ============================================
        // NOTIFICATIONS TABLE
        // ============================================
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->string('notifiable_type');
                $table->unsignedBigInteger('notifiable_id');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_type_notifiable_id_index');
            });
        }

        // ============================================
        // SMS_LOGS TABLE
        // ============================================
        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('recipient_phone');
                $table->text('message');
                $table->string('status')->default('pending');
                $table->text('gateway_response')->nullable();
                $table->string('gateway_message_id')->nullable();
                $table->string('notifiable_type')->nullable();
                $table->unsignedBigInteger('notifiable_id')->nullable();
                $table->uuid('notification_id')->nullable();
                $table->decimal('cost', 8, 2)->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                
                $table->index(['notifiable_type', 'notifiable_id']);
                $table->index('status');
            });
        }

        // ============================================
        // PUSH_SUBSCRIPTIONS TABLE (for WebPush compatibility)
        // ============================================
        if (!Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->string('subscribable_type');
                $table->unsignedBigInteger('subscribable_id');
                $table->string('endpoint', 500)->unique();
                $table->string('public_key')->nullable();
                $table->string('auth_token')->nullable();
                $table->string('content_encoding')->nullable();
                $table->timestamps();
                
                $table->index(['subscribable_type', 'subscribable_id'], 'push_subscriptions_subscribable_morph_idx');
            });
        }

        // ============================================
        // PATIENT_GROWTH_RECORDS TABLE
        // ============================================
        if (!Schema::hasTable('patient_growth_records')) {
            Schema::create('patient_growth_records', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('patient_id');
                $table->decimal('height', 5, 2)->nullable();
                $table->decimal('weight', 5, 2)->nullable();
                $table->date('recorded_date');
                $table->unsignedInteger('recorded_by')->nullable();
                $table->enum('measurement_type', ['birth', 'routine_checkup', 'vaccination_visit', 'sick_visit'])->default('routine_checkup');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index('patient_id');
                $table->index('recorded_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is additive - we don't want to remove columns
        // as it could cause data loss
    }

    /**
     * Helper: Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))->pluck('Key_name')->unique()->toArray();
        
        if (!in_array($indexName, $indexes)) {
            Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                $table->index($columns, $indexName);
            });
        }
    }

    /**
     * Helper: Add unique constraint if it doesn't exist
     */
    private function addUniqueIfNotExists(string $table, string $indexName, array $columns): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))->pluck('Key_name')->unique()->toArray();
        
        if (!in_array($indexName, $indexes)) {
            Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                $table->unique($columns, $indexName);
            });
        }
    }

    /**
     * Helper: Add foreign key if it doesn't exist
     */
    private function addForeignKeyIfNotExists(string $table, string $fkName, string $column, string $refTable, string $refColumn): void
    {
        $dbName = config('database.connections.mysql.database');
        
        $fks = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
        ", [$dbName, $table, $fkName]);
        
        if (count($fks) === 0 && Schema::hasTable($refTable)) {
            try {
                Schema::table($table, function (Blueprint $table) use ($fkName, $column, $refTable, $refColumn) {
                    $table->foreign($column, $fkName)->references($refColumn)->on($refTable)->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might fail if data integrity issues exist - log and continue
                Log::warning("Could not add foreign key {$fkName}: " . $e->getMessage());
            }
        }
    }
};
