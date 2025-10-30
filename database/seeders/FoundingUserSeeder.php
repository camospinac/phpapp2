<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Cmixin\BusinessDay; // Importante: Asegúrate de tener este paquete (cmixin/business-day)
use Illuminate\Support\Str;

class RealisticDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating 10 realistic test users with subscriptions...');

        // Preparamos los planes
        $plans = Plan::all();
        if ($plans->isEmpty()) {
            $this->command->error('No plans found. Please run PlanSeeder first.');
            return;
        }

        // Usamos User::withoutEvents para evitar que los Observers se disparen
        // y podamos crear data pasada sin activar la lógica de "hoy".
        User::withoutEvents(function () use ($plans) {
            
            // Creamos 10 usuarios de prueba realistas
            for ($j = 0; $j < 10; $j++) {
                
                // --- 1. CREAMOS EL USUARIO ---
                $nombres = fake()->firstName();
                $apellidos = fake()->lastName() . ' ' . fake()->lastName();
                $documento = fake()->unique()->numerify('##########');
                $passwordBase = Str::ucfirst($nombres) . $documento; // Lógica: NombreCedula

                $user = User::create([
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'documento' => $documento,
                    'email' => fake()->unique()->safeEmail(),
                    'telefono' => fake()->numerify('310#######'),
                    'password' => Hash::make($passwordBase),
                    'referral_code' => $this->generateUniqueReferralCode(),
                    'email_verified_at' => now(),
                    'rol' => 'user',
                    'es_cuenta_prueba' => false, // <-- IMPORTANTE: Estas SÍ se cuentan
                ]);
                $this->command->info("User {$user->nombres} created. (Pass: {$passwordBase})");

                // --- 2. CREAMOS SUS SUSCRIPCIONES (entre 1 y 3) ---
                $creationDate = Carbon::now()->subDays(rand(20, 90)); // Empezó hace 20-90 días
                $numSubscriptions = rand(1, 3);

                for ($i = 0; $i < $numSubscriptions; $i++) {
                    $plan = $plans->random();
                    // 1 de cada 4 será cerrada
                    $contractType = (rand(1, 4) == 1) ? 'cerrada' : 'abierta'; 
                    
                    $subscription = $user->subscriptions()->create([
                        'plan_id' => $plan->id,
                        'sequence_id' => $i + 1,
                        'initial_investment' => rand(5, 30) * 100000, // Entre 500k y 3M
                        'status' => 'active',
                        'contract_type' => $contractType,
                        'created_at' => $creationDate, // Fecha de creación pasada
                        'updated_at' => $creationDate,
                    ]);

                    // --- 3. GENERAMOS SUS PAGOS (Copiando la lógica del Trait) ---
                    $this->createPaymentSchedule($subscription);
                    
                    // Avanzamos en el tiempo para la siguiente suscripción
                    $creationDate->addDays(rand(7, 20)); 
                }
            }
        });
    }

    /**
     * Genera un código de referido único.
     */
    private function generateUniqueReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (User::where('referral_code', $code)->exists());
        return $code;
    }

    /**
     * Copiamos la lógica de `CreatesPaymentSchedules` aquí para usarla en el seeder
     * Esto es idéntico a la lógica de tu ejemplo.
     */
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
            // La lógica de "primer pago a 15 días" de tu Trait
            $currentDueDate = $startDate->copy()->addDays(15);
            // El trait no parece tener la lógica de ajustar a día hábil, pero la mantendré
            // if (!$currentDueDate->isBusinessDay()) { $currentDueDate = $currentDueDate->nextBusinessDay(); }

            if ($plan->calculation_type === 'fixed_plus_final' && $plan->fixed_percentage) {
                $fixedPayment = $amount * ($plan->fixed_percentage / 100);
                $totalProfit = $fixedPayment * 6;
                for ($i = 1; $i <= 5; $i++) {
                    $subscription->payments()->create(['amount' => $fixedPayment, 'percentage' => $plan->fixed_percentage, 'status' => 'pending', 'payment_due_date' => $currentDueDate->toDateString()]);
                    $currentDueDate->addDays(15);
                    // if (!$currentDueDate->isBusinessDay()) { $currentDueDate = $currentDueDate->nextBusinessDay(); }
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
                    // if (!$currentDueDate->isBusinessDay()) { $currentDueDate = $currentDueDate->nextBusinessDay(); }
                }
            }
        }
        $subscription->profit_amount = $totalProfit;
        $subscription->save();

        // --- LÓGICA ADICIONAL PARA SIMULAR PAGOS ---
        // Marcamos algunos pagos como 'paid' o 'accredited' si su fecha ya pasó
        $today = Carbon::now();
        foreach ($subscription->payments as $payment) {
            if ($payment->status === 'pending' && Carbon::parse($payment->payment_due_date)->isPast()) {
                // Simulamos que algunos se pagaron y otros se acreditaron al balance
                $newStatus = (rand(1, 3) == 1) ? 'accredited' : 'paid'; 
                $payment->status = $newStatus;
                $payment->save();

                // Si fue 'accredited', creamos la transacción de abono
                if ($newStatus === 'accredited') {
                    $subscription->user->transactions()->create([
                        'tipo' => 'abono',
                        'monto' => $payment->amount,
                        'observacion' => 'Abono de pago #' . $payment->id . ' de la inversión #' . $subscription->sequence_id,
                        'type_detail' => 'payment_accredited',
                        'subscription_id' => $subscription->id,
                        'created_at' => $payment->payment_due_date,
                        'updated_at' => $payment->payment_due_date,
                    ]);
                }
            }
        }
    }
}