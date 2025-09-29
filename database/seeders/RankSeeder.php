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
            'reward_description' => 'Bono del 10% sobre la inversión de tus referidos',
            'reward_percentage' => 10.00,
        ]);
        Rank::create([
            'name' => 'Plata',
            'required_referrals' => 25,
            'reward_description' => 'Bono del 10% sobre la inversión de tus referidos',
            'reward_percentage' => 10.00,
        ]);
    }
}
