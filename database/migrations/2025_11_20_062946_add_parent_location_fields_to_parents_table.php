<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Parents;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns already exist and add them if they don't
        if (!Schema::hasColumn('parents', 'barangay')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->string('barangay')->nullable()->after('email');
            });
        }
        
        if (!Schema::hasColumn('parents', 'address')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->text('address')->nullable()->after('email');
            });
        }
        
        if (!Schema::hasColumn('parents', 'contact_number')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->string('contact_number')->nullable()->after('email');
            });
        }

        // Populate parent location data from first child
        $parents = Parents::with('patients')->get();
        foreach ($parents as $parent) {
            if ($parent->patients->isNotEmpty()) {
                $firstChild = $parent->patients->first();
                $parent->update([
                    'barangay' => $firstChild->barangay ?? 'Unknown',
                    'address' => $firstChild->address,
                    'contact_number' => $firstChild->contact_no,
                ]);
            } else {
                // For parents without children, set a default value
                $parent->update([
                    'barangay' => 'Unknown',
                ]);
            }
        }

        // Make barangay required after population
        Schema::table('parents', function (Blueprint $table) {
            $table->string('barangay')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn(['barangay', 'address', 'contact_number']);
        });
    }
};
