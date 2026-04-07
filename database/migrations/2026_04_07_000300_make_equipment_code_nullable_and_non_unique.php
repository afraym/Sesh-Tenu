<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE equipment DROP INDEX equipment_equipment_code_unique');
        DB::statement('ALTER TABLE equipment MODIFY equipment_code VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE equipment MODIFY equipment_code VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE equipment ADD UNIQUE KEY equipment_equipment_code_unique (equipment_code)');
    }
};
