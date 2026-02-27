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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('project_name'); // اسم المشروع
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade'); // اسم الشركة
            $table->string('previous_driver')->nullable(); // اسم السائق السابق
            $table->string('current_driver')->nullable(); // اسم السائق
            $table->string('equipment_type'); // نوع المعدة
            $table->string('model_year')->nullable(); // موديل المعدة
            $table->string('equipment_code')->unique(); // كود المعدة
            $table->string('equipment_number')->nullable(); // رقم شاسية المعدة
            $table->string('manufacture')->nullable(); // المصنع
            $table->string('entry_per_ser')->nullable(); // تصريح الدخول
            $table->string('reg_no')->nullable(); // رقم التسجيل
            $table->string('equip_reg_issue')->nullable(); // رقم رخصة المعدة
            $table->string('custom_clearance')->nullable(); // الافراج الجمركي
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
