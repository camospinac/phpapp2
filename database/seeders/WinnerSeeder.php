<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class WinnerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating test users for winners...');

        // 1. Create a collection of fake users first
        $users = User::factory(20)->create([
            'rol' => 'usuario',
            'password' => Hash::make('password'),
        ]);

        $this->command->info('Test users created.');
        $this->command->info('Creating winner records...');

        // 2. Set the start date and end date
        $startDate = Carbon::create(2025, 6, 15);
        $endDate = Carbon::today();
        $currentDate = $startDate->copy();

        $prizes = ['Viaje a Cartagena', 'iPhone 15', 'Bono de $200.000', 'Smart TV 55"', 'Air Fyer'];
        $cities = ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Girardot'];

        // 3. Loop every 15 days from the start date until today
        while ($currentDate->lessThanOrEqualTo($endDate)) {
            // Create two winner records for the current date
            for ($i = 0; $i < 2; $i++) {
                Winner::create([
                    'user_id' => $users->random()->id,
                    'win_date' => $currentDate,
                    'prize' => $prizes[array_rand($prizes)],
                    'city' => $cities[array_rand($cities)],
                ]);
            }
            // Move to the next date
            $currentDate->addDays(15);
        }

        $this->command->info('Winner records created successfully.');
    }
}