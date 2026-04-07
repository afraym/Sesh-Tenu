<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update all equipment records with random model_year between 2000 and 2009
        DB::statement("
            UPDATE equipment 
            SET model_year = CONCAT('', FLOOR(RAND() * 10) + 2000)
            WHERE model_year IS NULL OR model_year = ''
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE equipment 
            SET model_year = NULL
            WHERE model_year BETWEEN '2000' AND '2009'
        ");
    }
};
