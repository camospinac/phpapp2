<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Cmixin\BusinessDay;

class RealisticUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando usuarios de prueba con la nueva lógica...');

        $plans = Plan::all();
        if ($plans->isEmpty()) {
            $this->command->error('No se encontraron planes. Por favor, corre el PlanSeeder primero.');
            return;
        }

        // 🔹 Lista actualizada de usuarios
        $usersData = [
            [
                'nombres' => 'Cristian Leonardo',
                'apellidos' => 'Ramírez Guzmán',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '1070625672',
                'email' => 'Cristianleonardoramirez13@gmail.com',
                'celular' => '7147476681',
                'location' => 'Colombia, Cundinamarca, Girardot',
                'password_base' => 'Cristian1070625672',
            ],
            [
                'nombres' => 'Sergio',
                'apellidos' => 'Cárdenas',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '80548813',
                'email' => 'sergiocardenas3225@gmail.com',
                'celular' => '3107973703',
                'location' => 'Colombia, Cundinamarca, Girardot',
                'password_base' => 'Sergio80548813',
            ],
            [
                'nombres' => 'Andrea',
                'apellidos' => 'Rubiano Garzon',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '35426560',
                'email' => 'andrearubga1983@gmail.com',
                'celular' => '3102116914',
                'location' => 'Colombia, Cundinamarca, Girardot',
                'password_base' => 'Andrea35426560',
            ],
            [
                'nombres' => 'Andrea',
                'apellidos' => 'González Gonzalez',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '1110519752',
                'email' => 'andreag408@hotmail.com',
                'celular' => '3102872389',
                'location' => 'Colombia, Cundinamarca, Girardot',
                'password_base' => 'Andrea1110519752',
            ],
            [
                'nombres' => 'German Alberto',
                'apellidos' => 'Martinez Suarez',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '80179115',
                'email' => 'monstwer24@gmail.com',
                'celular' => '3229484560',
                'location' => 'Colombia, Cundinamarca, Girardot',
                'password_base' => 'German80179115',
            ],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Álvarez',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '11524015',
                'email' => 'carlosalvarezbus@gmail.com',
                'celular' => '3229484570',
                'location' => 'Colombia, Cundinamarca, Girardot',
                'password_base' => 'Carlos11524015',
            ],
            // 🚀 NUEVO USUARIO
            [
                'nombres' => 'Hansel',
                'apellidos' => 'Rodríguez Hernández',
                'identification_type' => 'CEDULA CIUDANIA',
                'identification_number' => '1075654215',
                'email' => 'hanselfbi@hotmail.com',
                'celular' => '3012840212',
                'location' => 'Colombia, Cundinamarca, Zipaquirá',
                'password_base' => 'Hansel1075654215',
            ],
        ];

        foreach ($usersData as $userData) {
            User::withoutEvents(function () use ($userData, $plans) {
                $existing = User::where('email', $userData['email'])->first();
                if ($existing) {
                    $this->command->warn("El usuario {$userData['email']} ya existe, saltando...");
                    return;
                }

                $user = User::create([
                    'nombres' => $userData['nombres'],
                    'apellidos' => $userData['apellidos'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password_base']),
                    'rol' => 'usuario',
                    'es_cuenta_prueba' => true,
                    'location' => $userData['location'],
                    'identification_type' => $userData['identification_type'],
                    'identification_number' => $userData['identification_number'],
                    'celular' => $userData['celular'],
                    'referral_code' => strtoupper(substr($userData['nombres'], 0, 4) . rand(1000, 9999)),
                ]);

                $this->command->info("✅ Usuario {$user->nombres} creado.");

                // --- 2. Crear 2 suscripciones con antigüedad de 30-32 días ---
                $creationDate = Carbon::now()->subDays(rand(30, 32));

                for ($i = 0; $i < 2; $i++) {
                    $plan = $plans->random();
                    // Una abierta y una cerrada
                    $contractType = ($i == 0) ? 'abierta' : 'cerrada';

                    $subscription = $user->subscriptions()->create([
                        'plan_id' => $plan->id,
                        'sequence_id' => $i + 1,
                        'initial_investment' => rand(10, 20) * 100000, // Entre 1.0M y 2.0M
                        'status' => 'active',
                        'contract_type' => $contractType,
                        'created_at' => $creationDate,
                        'updated_at' => $creationDate,
                    ]);

                    $this->createPaymentSchedule($subscription);
                }
            });
        }
    }

    protected function createPaymentSchedule(Subscription $subscription)
    {
        $plan = $subscription->plan;
        $amount = $subscription->initial_investment;
        $totalProfit = 0;

        $startDate = Carbon::parse($subscription->created_at);
        BusinessDay::enable('Carbon\Carbon', 'es_CO');

        if ($subscription->contract_type === 'cerrada') {
            $profitPercentage = $plan->closed_profit_percentage ?? 50;
            $durationDays = $plan->closed_duration_days ?? 90;
            $baseProfit = $amount * ($profitPercentage / 100);
            $totalProfit = $baseProfit; // Ajustado a la lógica de tu negocio
            $totalPayment = $amount + $totalProfit;
            $dueDate = $startDate->copy()->addDays($durationDays);

            $subscription->payments()->create([
                'amount' => $totalPayment,
                'percentage' => $profitPercentage,
                'status' => 'pending',
                'payment_due_date' => $dueDate->toDateString(),
            ]);
        } else {
            $currentDueDate = $startDate->copy()->addDays(15);
            if (!$currentDueDate->isBusinessDay()) {
                $currentDueDate = $currentDueDate->nextBusinessDay();
            }

            if ($plan->calculation_type === 'fixed_plus_final' && $plan->fixed_percentage) {
                $fixedPayment = $amount * ($plan->fixed_percentage / 100);
                $totalProfit = $fixedPayment * 6;
                for ($i = 1; $i <= 5; $i++) {
                    $subscription->payments()->create([
                        'amount' => $fixedPayment,
                        'percentage' => $plan->fixed_percentage,
                        'status' => 'pending',
                        'payment_due_date' => $currentDueDate->toDateString(),
                    ]);
                    $currentDueDate->addDays(15);
                }
                $finalPayment = $amount + $fixedPayment;
                $subscription->payments()->create([
                    'amount' => $finalPayment,
                    'percentage' => null,
                    'status' => 'pending',
                    'payment_due_date' => $currentDueDate->toDateString(),
                ]);
            } elseif ($plan->calculation_type === 'equal_installments' && $plan->fixed_percentage) {
                $fixedPayment = $amount * ($plan->fixed_percentage / 100);
                $totalProfit = $fixedPayment * 6;
                $installment = ($amount + $totalProfit) / 6;

                for ($i = 1; $i <= 6; $i++) {
                    $subscription->payments()->create([
                        'amount' => $installment,
                        'percentage' => null,
                        'status' => 'pending',
                        'payment_due_date' => $currentDueDate->toDateString(),
                    ]);
                    $currentDueDate->addDays(15);
                }
            }
        }

        $subscription->profit_amount = $totalProfit;
        $subscription->save();
    }
}