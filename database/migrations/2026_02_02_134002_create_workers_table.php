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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('worker_name');
            $table->string('mobile_number');
            $table->string('id_number')->unique();
            $table->foreignId('job_type_id')->constrained()->onDelete('cascade');
            $table->string('access_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
