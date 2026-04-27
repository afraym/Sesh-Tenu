<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Basic access for small teams that need the core tools.',
                'price' => 199.00,
                'billing_cycle_months' => 1,
                'features' => [
                    'Up to 10 workers',
                    'Core equipment tracking',
                    'Basic document management',
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Full access for growing teams with operational workflows.',
                'price' => 499.00,
                'billing_cycle_months' => 1,
                'features' => [
                    'Unlimited workers',
                    'Equipment and project management',
                    'Daily delivery tracking',
                    'Priority support',
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Multi-company access with premium support and custom setup.',
                'price' => 999.00,
                'billing_cycle_months' => 1,
                'features' => [
                    'All Professional features',
                    'Custom onboarding',
                    'Dedicated account support',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}