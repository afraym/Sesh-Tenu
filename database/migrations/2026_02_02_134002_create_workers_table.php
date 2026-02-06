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
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // الشركة
            $table->string('name'); // الاسم
            $table->string('entity')->nullable(); // الهيئة
            $table->foreignId('job_type_id')->nullable()->constrained()->onDelete('set null'); // الوظيفة
            $table->string('national_id')->unique(); // الرقم القومي
            $table->string('phone_number'); // رقم الهاتف
            $table->boolean('has_housing')->default(false); // هل متوفر له سكن
            $table->boolean('is_local_community')->default(false); // هل من المجتمع المحلي
            $table->text('address')->nullable(); // العنوان
            $table->date('join_date')->nullable(); // تاريخ الانضمام
            $table->date('end_date')->nullable(); // تاريخ الانهاء
            $table->boolean('is_on_company_payroll')->default(true); // هل على قوة الشركة
            $table->decimal('salary', 10, 2)->nullable(); // الراتب
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
