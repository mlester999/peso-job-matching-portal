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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->references('id')->on('applicants');
            $table->foreignId('job_advertisement_id')->references('id')->on('job_advertisements')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('sex')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('barangay')->nullable();
            $table->string('street_address')->nullable();
            $table->string('zip_code')->nullable();
            $table->json('education')->nullable();
            $table->json('work_experience')->nullable();
            $table->json('list_of_credentials')->nullable();
            $table->json('skills')->nullable();
            $table->tinyInteger('status');
            $table->tinyInteger('is_draft')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
