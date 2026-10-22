<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add stocks column to vaccines table
        Schema::table('vaccines', function (Blueprint $table) {
            $table->integer('stocks')->default(0)->after('doses_description');
        });

        // Transfer data from inventories to vaccines table
        $inventories = DB::table('inventories')->get();
        
        foreach ($inventories as $inventory) {
            // Find matching vaccine by name
            $vaccine = DB::table('vaccines')
                ->where('vaccine_name', $inventory->vaccine_name)
                ->first();
            
            if ($vaccine) {
                // Update existing vaccine with stock info
                DB::table('vaccines')
                    ->where('id', $vaccine->id)
                    ->update(['stocks' => $inventory->stocks]);
            } else {
                // Create new vaccine record if it doesn't exist
                DB::table('vaccines')->insert([
                    'vaccine_name' => $inventory->vaccine_name,
                    'doses_description' => '', // Default empty description
                    'stocks' => $inventory->stocks,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Drop the inventories table as it's no longer needed
        Schema::dropIfExists('inventories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate inventories table
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('vaccine_name');
            $table->integer('stocks');
            $table->timestamps();
        });

        // Transfer data back from vaccines to inventories
        $vaccines = DB::table('vaccines')->where('stocks', '>', 0)->get();
        
        foreach ($vaccines as $vaccine) {
            DB::table('inventories')->insert([
                'vaccine_name' => $vaccine->vaccine_name,
                'stocks' => $vaccine->stocks,
                'created_at' => $vaccine->created_at,
                'updated_at' => $vaccine->updated_at,
            ]);
        }

        // Remove stocks column from vaccines table
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropColumn('stocks');
        });
    }
};
