<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Seeder;

class FirstProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Create (or find) the first company
        $company = Company::firstOrCreate(
            ['name' => 'شركة شيماء'],
            [
                'short_name' => 'شيماء',
                'address'     => null,
                'phone'       => null,
                'logo'        => null,
            ]
        );

        // Create (or find) the first project linked to that company
        $project = Project::firstOrCreate(
            ['name' => ' محطة كهرباء أبيدوس2 للطاقة الشمسية بقدرة 1000 ميجاوات 
PV Power Plant Abydos 2 Solar (MW1000)'],
            [
                'short_name' => 'م-1',
                'company_id' => $company->id,
            ]
        );

        $this->command->info("Company ready:  [{$company->id}] {$company->name}");
        $this->command->info("Project ready:  [{$project->id}] {$project->name}");
    }
}
