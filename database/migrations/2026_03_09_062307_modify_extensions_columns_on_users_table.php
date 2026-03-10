<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration is a no-op. The first migration already created the string columns correctly.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is a no-op, nothing to revert.
    }
};
