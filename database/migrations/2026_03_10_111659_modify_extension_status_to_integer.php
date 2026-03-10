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
        // First, update any NULL or non-numeric values to 0
        DB::statement("UPDATE extensions SET status = 0 WHERE status IS NULL OR status = '' OR status NOT REGEXP '^[0-9]+$'");

        Schema::table('extensions', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->string('status')->nullable()->change();
        });
    }
};
