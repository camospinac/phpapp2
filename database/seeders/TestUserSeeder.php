<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::withoutEvents(function () {
            // --- USUARIO 1: CARLOS ALVAREZ ---
            $user1 = User::create([
                'nombres' => 'Carlos',
                'apellidos' => 'Alvarez',
                'email' => 'carlosalvarezbus@gmail.com',
                'password' => Hash::make('password'),
                'rol' => 'usuario',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '1234567890',
                'celular' => '3001234567',
                'referral_code' => 'CARLOS12',
            ]);
            $this->command->info('Usuario de prueba Carlos Alvarez creado.');

            Transaction::create([
                'id_user' => $user1->id,
                'tipo' => 'abono',
                'monto' => 8000000,
                'observacion' => 'Saldo inicial de prueba',
            ]);
            $this->command->info('Saldo disponible para Carlos añadido.');

            // --- USUARIO 2: AJUSTA ESTOS DATOS ---
            $user2 = User::create([
                'nombres' => 'Cristian',
                'apellidos' => 'Guzman',
                'email' => 'cristianleonardoramirez13@gmail.com',
                'password' => Hash::make('password'),
                'rol' => 'usuario',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '1098765432',
                'celular' => '3017654321',
                'referral_code' => 'CRISG23',
            ]);
            $this->command->info('Usuario de prueba CRISTIAN creado.');

             Transaction::create([
                'id_user' => $user2->id,
                'tipo' => 'abono',
                'monto' => 9000000,
                'observacion' => 'Saldo inicial de prueba',
            ]);
            $this->command->info('Saldo disponible para CRIS añadido.');
            
            // (Optional) Add transactions or subscriptions for Ana here if you want

            // --- USUARIO 3: AJUSTA ESTOS DATOS ---
            $user3 = User::create([
                'nombres' => 'German',
                'apellidos' => 'Martinez',
                'email' => 'monstwer24@gmail.com',
                'password' => Hash::make('password'),
                'rol' => 'usuario',
                'identification_type' => 'PASAPORTE',
                'identification_number' => 'A1B2C3D4',
                'celular' => '3201239876',
                'referral_code' => 'GERMA45',
            ]);
            $this->command->info('Usuario de prueba GERMAN creado.');

            Transaction::create([
                'id_user' => $user3->id,
                'tipo' => 'abono',
                'monto' => 6000000,
                'observacion' => 'Saldo inicial de prueba',
            ]);
            $this->command->info('Saldo disponible para GERMAN añadido.');


            $user4 = User::create([
                'nombres' => 'Sergio',
                'apellidos' => 'Cardenas',
                'email' => 'sergiocardenas32252@gmail.com',
                'password' => Hash::make('password'),
                'rol' => 'usuario',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '1098765432',
                'celular' => '3017654321',
                'referral_code' => 'SERC89',
            ]);

            Transaction::create([
                'id_user' => $user4->id,
                'tipo' => 'abono',
                'monto' => 8000000,
                'observacion' => 'Saldo inicial de prueba',
            ]);
            $this->command->info('Saldo disponible para SERGIO añadido.');

            // (Optional) Add transactions or subscriptions for Luis here
        });
    }
}