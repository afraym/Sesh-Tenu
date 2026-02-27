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
            ]
        );

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Email: afraymn@gmail.com');
        $this->command->info('Password: 12345678');
    }
}
