<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('extensions', function (Blueprint $table) {
            // Drop the old unique constraint by index name
            DB::statement('ALTER TABLE extensions DROP INDEX extensions_company_id_number_unique');

            // Add the new unique constraint that includes extension_type_id
            $table->unique(['company_id', 'number', 'extension_type_id']);
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('extensions', function (Blueprint $table) {
            // Drop the new unique constraint
            DB::statement('ALTER TABLE extensions DROP INDEX extensions_company_id_number_extension_type_id_unique');

            // Restore the old constraint
            $table->unique(['company_id', 'number']);
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
