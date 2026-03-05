<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `worker_document_deliveries` MODIFY COLUMN `shift` ENUM('morning','evening','mixed') COLLATE utf8mb4_unicode_ci DEFAULT 'morning'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `worker_document_deliveries` MODIFY COLUMN `shift` ENUM('morning','evening') COLLATE utf8mb4_unicode_ci DEFAULT 'morning'"
        );
    }
};
