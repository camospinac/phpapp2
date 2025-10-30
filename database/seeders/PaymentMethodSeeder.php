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
            ['account_details' => 'Adjustar ADMIN', 'is_active' => true, 'logo_path' => 'logos/nequi.jpg']
        );

        PaymentMethod::updateOrCreate(
            ['name' => 'Daviplata'],
            ['account_details' => 'Adjustar ADMIN', 'is_active' => true, 'logo_path' => 'logos/daviplata.png']
        );

        PaymentMethod::updateOrCreate(
            ['name' => 'Bre-B'],
            ['account_details' => 'Adjustar ADMIN', 'is_active' => true, 'logo_path' => 'logos/breb.png']
        );

        PaymentMethod::updateOrCreate(
            ['name' => 'Movii'],
            ['account_details' => 'Adjustar ADMIN', 'is_active' => true, 'logo_path' => 'logos/movi.jpg']
        );
    }
}
