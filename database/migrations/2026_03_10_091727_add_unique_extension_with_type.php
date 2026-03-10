<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds a unique constraint on (company_id, number, extension_type_id).
     * This allows duplicate extension numbers as long as they have different types within the same company.
     * The old unique constraint on (company_id, number) will remain for backward compatibility.
     */
    public function up(): void
    {
        // Add new unique constraint on (company_id, number, extension_type_id)
        // This allows the same extension number to exist multiple times if they have different types
        DB::statement('ALTER TABLE extensions ADD UNIQUE KEY extensions_company_id_number_extension_type_id_unique (company_id, number, extension_type_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new unique constraint
        DB::statement('ALTER TABLE extensions DROP INDEX extensions_company_id_number_extension_type_id_unique');
    }
};
