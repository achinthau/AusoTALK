<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration is now redundant as the unique constraint was already handled
        // by later migrations. Skip execution.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is now redundant. Skip execution.
    }
};
