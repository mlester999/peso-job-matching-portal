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
        Schema::create('job_advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_position_id')->references('id')->on('job_positions');
            $table->string('role')->nullable();
            $table->string('position_level')->nullable();
            $table->string('years_of_experience')->nullable();
            $table->boolean('is_draft')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_advertisements');
    }
};