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
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['runtime', 'static']);
            $table->string('title');
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'positive']);
            $table->string('location')->nullable();
            $table->text('evidence')->nullable();
            $table->text('ai_explanation')->nullable();
            $table->text('ai_attack_scenario')->nullable();
            $table->text('fix_recommendation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};
