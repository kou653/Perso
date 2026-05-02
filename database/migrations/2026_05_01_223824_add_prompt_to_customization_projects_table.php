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
        Schema::table('customization_projects', function (Blueprint $table) {
            $table->text('prompt')->nullable()->after('customization_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customization_projects', function (Blueprint $table) {
            $table->dropColumn('prompt');
        });
    }
};
