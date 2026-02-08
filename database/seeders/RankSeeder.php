<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rank;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        Rank::create([
            'name' => 'Bronce',
            'required_referrals' => 10,
            'reward_description' => 'Bono de 400.000 COP',
            'reward_amount' => 400000.00,
        ]);
        Rank::create([
            'name' => 'Plata',
            'required_referrals' => 20,
            'reward_description' => 'Bono de 600.000 COP',
            'reward_amount' => 600000.00,
        ]);

        Rank::create([
            'name' => 'Oro',
            'required_referrals' => 30,
            'reward_description' => 'Bono de 1.000.000 COP',
            'reward_amount' => 1000000.00,
        ]);
    }
}
