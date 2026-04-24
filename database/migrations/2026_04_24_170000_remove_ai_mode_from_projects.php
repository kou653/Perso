<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_generations')) {
            Schema::drop('ai_generations');
        }

        Schema::table('customization_projects', function (Blueprint $table): void {
            if (Schema::hasColumn('customization_projects', 'prompt')) {
                $table->dropColumn('prompt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customization_projects', function (Blueprint $table): void {
            $table->text('prompt')->nullable()->after('customization_data');
        });

        Schema::create('ai_generations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customization_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_template_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('queued');
            $table->text('prompt');
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }
};
