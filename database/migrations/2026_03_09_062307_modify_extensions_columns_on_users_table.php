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
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraints using raw SQL
            DB::statement('ALTER TABLE users DROP FOREIGN KEY users_primary_extension_id_foreign');
            DB::statement('ALTER TABLE users DROP FOREIGN KEY users_secondary_extension_id_foreign');
        });

        // Now drop the columns and add new ones
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['primary_extension_id', 'secondary_extension_id']);
            $table->string('primary_extension')->nullable();
            $table->string('secondary_extension')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the string columns
            $table->dropColumn(['primary_extension', 'secondary_extension']);
            // Re-add the foreign key columns
            $table->foreignId('primary_extension_id')->nullable()->constrained('extensions')->nullableOnDelete();
            $table->foreignId('secondary_extension_id')->nullable()->constrained('extensions')->nullableOnDelete();
        });
    }
};
