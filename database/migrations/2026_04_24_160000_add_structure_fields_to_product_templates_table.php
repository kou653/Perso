<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_templates', function (Blueprint $table): void {
            $table->json('layout')->nullable()->after('preview_data');
            $table->json('default_values')->nullable()->after('layout');
        });
    }

    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table): void {
            $table->dropColumn(['layout', 'default_values']);
        });
    }
};
