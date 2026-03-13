<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'afraymn@gmail.com'],
            [
                'name' => 'Super Admin',
                'email' => 'afraymn@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => 'super_admin',
                'company_id' => null,
            ],
            ['email' => '01151543119'],
            [
                'name' => 'Khaled Atef',
                'email' => '01151543119',
                'password' => bcrypt('01151543119'),
                'role' => 'company_owner',
                'company_id' => 1,
            ]
        );

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Super Admin created successfully!');
        $this->command->info('Email: afraymn@gmail.com');
        $this->command->info('Password: 12345678');
        $this->command->info('Email: 01151543119');
        $this->command->info('Password: 01151543119');
    }
}
