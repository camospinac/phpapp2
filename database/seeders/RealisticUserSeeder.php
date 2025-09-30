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
        $this->command->info('Creating realistic test users...');

        // Preparamos los datos de los planes para no consultarlos en cada iteración
        $plans = Plan::all();
        if ($plans->isEmpty()) {
            $this->command->error('No plans found. Please run PlanSeeder first.');
            return;
        }

        // Datos de los usuarios a crear
        $usersData = [
            ['nombres' => 'Carlos', 'apellidos' => 'Alvarez', 'email' => 'carlosalvarezbus@gmail.com'],
            ['nombres' => 'Cristian', 'apellidos' => 'Ramirez', 'email' => 'cristianleonardoramirez13@gmail.com'],
            ['nombres' => 'Sergio', 'apellidos' => 'Cardenas', 'email' => 'sergiocardenas32252@gmail.com'],
            ['nombres' => 'German', 'apellidos' => 'Ortiz', 'email' => 'monstwer24@gmail.com'], // Añadí un apellido
        ];

        foreach ($usersData as $userData) {
            User::withoutEvents(function () use ($userData, $plans) {
                // --- 1. CREAMOS EL USUARIO ---
                $user = User::create([
                    'nombres' => $userData['nombres'],
                    'apellidos' => $userData['apellidos'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'rol' => 'usuario',
                    'identification_type' => 'CEDULA CIUDANIA',
                    'identification_number' => fake()->unique()->numerify('##########'),
                    'celular' => fake()->numerify('300#######'),
                    'referral_code' => strtoupper(substr($userData['nombres'], 0, 4) . rand(1000, 9999)),
                ]);
                $this->command->info("User {$user->nombres} created.");

                // --- 2. CREAMOS SUS 3 SUSCRIPCIONES CON FECHAS PASADAS ---
                $creationDate = Carbon::now()->subDays(45); // Empezamos hace 45 días

                for ($i = 0; $i < 3; $i++) {
                    $plan = $plans->random();
                    // La última suscripción será cerrada
                    $contractType = ($i == 2) ? 'cerrada' : 'abierta'; 
                    
                    $subscription = $user->subscriptions()->create([
                        'plan_id' => $plan->id,
                        'sequence_id' => $i + 1,
                        'initial_investment' => rand(10, 20) * 100000, // Entre 1M y 2M
                        'status' => 'active',
                        'contract_type' => $contractType,
                        'created_at' => $creationDate,
                        'updated_at' => $creationDate,
                    ]);

                    // --- 3. GENERAMOS SUS PAGOS (Copiando la lógica del Trait) ---
                    $this->createPaymentSchedule($subscription);
                    
                    // Avanzamos en el tiempo para la siguiente suscripción
                    $creationDate->addDays(10); 
                }
            });
        }
    }

    // Copiamos la lógica de `CreatesPaymentSchedules` aquí para usarla en el seeder
    protected function createPaymentSchedule(Subscription $subscription)
    {
        $plan = $subscription->plan;
        $amount = $subscription->initial_investment;
        $totalProfit = 0;

        // Usamos la fecha de creación de la suscripción como punto de partida
        $startDate = Carbon::parse($subscription->created_at); 

        if ($subscription->contract_type === 'cerrada') {
            $profitPercentage = $plan->closed_profit_percentage ?? 50;
            $durationDays = $plan->closed_duration_days ?? 90;
            $baseProfit = $amount * ($profitPercentage / 100);
            $totalProfit = $baseProfit * 3;
            $totalPayment = $amount + $totalProfit;
            $dueDate = $startDate->copy()->addDays($durationDays);
            $subscription->payments()->create([
                'amount' => $totalPayment, 'percentage' => $profitPercentage, 'status' => 'pending', 'payment_due_date' => $dueDate->toDateString()
            ]);
        } else { // 'abierta'
            BusinessDay::enable('Carbon\Carbon', 'es_CO');
            $currentDueDate = $startDate->copy()->addDays(15);
            if (!$currentDueDate->isBusinessDay()) { $currentDueDate = $currentDueDate->nextBusinessDay(); }

            if ($plan->calculation_type === 'fixed_plus_final' && $plan->fixed_percentage) {
                $fixedPayment = $amount * ($plan->fixed_percentage / 100);
                $totalProfit = $fixedPayment * 6;
                for ($i = 1; $i <= 5; $i++) {
                    $subscription->payments()->create(['amount' => $fixedPayment, 'percentage' => $plan->fixed_percentage, 'status' => 'pending', 'payment_due_date' => $currentDueDate->toDateString()]);
                    $currentDueDate->addDays(15);
                }
                $finalPayment = $amount + $fixedPayment;
                $subscription->payments()->create(['amount' => $finalPayment, 'percentage' => null, 'status' => 'pending', 'payment_due_date' => $currentDueDate->toDateString()]);
            } elseif ($plan->calculation_type === 'equal_installments' && $plan->fixed_percentage) {
                $fixedPayment = $amount * ($plan->fixed_percentage / 100);
                $totalProfit = $fixedPayment * 6;
                $installment = ($amount + $totalProfit) / 6;
                for ($i = 1; $i <= 6; $i++) {
                    $subscription->payments()->create(['amount' => $installment, 'percentage' => null, 'status' => 'pending', 'payment_due_date' => $currentDueDate->toDateString()]);
                    $currentDueDate->addDays(15);
                }
            }
        }
        $subscription->profit_amount = $totalProfit;
        $subscription->save();
    }
}