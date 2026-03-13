<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('driver_worker_id')
                  ->nullable()
                  ->after('driver_user_id')
                  ->constrained('workers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['driver_worker_id']);
            $table->dropColumn('driver_worker_id');
        });
    }
};
