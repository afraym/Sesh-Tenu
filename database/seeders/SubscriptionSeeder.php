<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $plan = Plan::where('slug', 'starter')->first();

        if (!$company || !$plan) {
            return;
        }

        Subscription::updateOrCreate(
            [
                'company_id' => $company->id,
                'status' => 'active',
            ],
            [
                'plan_id' => $plan->id,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'notes' => 'Seeded demo subscription.',
            ]
        );
    }
}