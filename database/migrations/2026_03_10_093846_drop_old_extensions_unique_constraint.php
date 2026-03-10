<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old unique constraint that only checks (company_id, number)
        // We now allow duplicate numbers if they have different extension_type_id
        DB::statement('ALTER TABLE extensions DROP INDEX extensions_company_id_number_unique');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the old unique constraint if needed
        DB::statement('ALTER TABLE extensions ADD UNIQUE KEY extensions_company_id_number_unique (company_id, number)');
    }
};
