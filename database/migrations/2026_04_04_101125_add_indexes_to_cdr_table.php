<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mysql-voice')->table('cdr', function (Blueprint $table) {
            // Index for filtering by context (dcontext) - commonly used in queries
            $table->index('dcontext');

            // Index for sorting and filtering by calldate - used in default sort
            $table->index('calldate');

            // Index for filtering by disposition
            $table->index('disposition');

            // Composite index for common query pattern: dcontext + calldate DESC
            $table->index(['dcontext', 'calldate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql-voice')->table('cdr', function (Blueprint $table) {
            $table->dropIndex(['dcontext']);
            $table->dropIndex(['calldate']);
            $table->dropIndex(['disposition']);
            $table->dropIndex(['dcontext', 'calldate']);
        });
    }
};
