<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::updateOrCreate(
            ['name' => 'Nequi'],
            ['account_details' => '316 780 9532', 'is_active' => true, 'logo_path' => 'logos/nequi.jpg']
        );

        PaymentMethod::updateOrCreate(
            ['name' => 'Bre-B'],
            ['account_details' => '316 780 9532', 'is_active' => true, 'logo_path' => 'logos/breb.png']
        );

    }
}
