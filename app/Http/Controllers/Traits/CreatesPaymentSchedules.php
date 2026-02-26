<?php

namespace App\Http\Controllers\Traits;

use App\Models\Subscription;
use Carbon\Carbon;

trait CreatesPaymentSchedules
{
    protected function createPaymentSchedule(Subscription $subscription)
    {
        // SEGURIDAD 1: Refrescamos el modelo para asegurar que initial_investment no sea nulo
        $subscription->refresh();
        
        $plan = $subscription->plan;
        $amount = (float) $subscription->initial_investment; // SEGURIDAD 2: Aseguramos que sea número
        $totalProfit = 0;

        // --- LÓGICA PARA CONTRATO CERRADO ---
        if ($subscription->contract_type === 'cerrada') {
            
            $profitPercentage = (float) ($plan->closed_profit_percentage ?? 50);
            $durationDays = (int) ($plan->closed_duration_days ?? 90);

            // Cálculo: (Capital * %Base) = Utilidad de 1 mes
            $baseProfit = $amount * ($profitPercentage / 100);
            
            // Utilidad Total = Utilidad de 1 mes * 3 meses
            $totalProfit = $baseProfit * 3;
            
            // PAGO TOTAL = CAPITAL + UTILIDAD TOTAL (Aquí estaba el fallo de algunos registros)
            $totalPayment = $amount + $totalProfit;
            
            $dueDate = Carbon::now()->addDays($durationDays);

            // Creamos el registro de pago único al final del periodo
            $subscription->payments()->create([
                'amount' => round($totalPayment, 2), // SEGURIDAD 3: Redondeo profesional
                'percentage' => $profitPercentage,
                'status' => 'pending',
                'payment_due_date' => $dueDate->toDateString(),
            ]);

        } else { 
            // --- LÓGICA PARA CONTRATO ABIERTO ---
            $currentDueDate = Carbon::now()->addDays(15);

            if ($plan->calculation_type === 'fixed_plus_final' && $plan->fixed_percentage) {
                $fixedPercentage = (float) $plan->fixed_percentage;
                $fixedPayment = $amount * ($fixedPercentage / 100);
                
                // 5 Pagos de utilidad pura
                for ($i = 1; $i <= 5; $i++) {
                    $subscription->payments()->create([
                        'amount' => round($fixedPayment, 2),
                        'percentage' => $fixedPercentage,
                        'status' => 'pending',
                        'payment_due_date' => $currentDueDate->toDateString(),
                    ]);
                    $currentDueDate->addDays(15);
                }

                // El 6to pago: CAPITAL + Utilidad final
                $finalPayment = $amount + $fixedPayment;
                $subscription->payments()->create([
                    'amount' => round($finalPayment, 2),
                    'percentage' => $fixedPercentage,
                    'status' => 'pending',
                    'payment_due_date' => $currentDueDate->toDateString(),
                ]);

                $totalProfit = $fixedPayment * 6;

            } elseif ($plan->calculation_type === 'equal_installments' && $plan->fixed_percentage) {
                $fixedPercentage = (float) $plan->fixed_percentage;
                $fixedPayment = $amount * ($fixedPercentage / 100);
                $totalProfit = $fixedPayment * 6;
                
                $totalToPay = $amount + $totalProfit;
                $installment = $totalToPay / 6;

                for ($i = 1; $i <= 6; $i++) {
                    $subscription->payments()->create([
                        'amount' => round($installment, 2),
                        'percentage' => null,
                        'status' => 'pending',
                        'payment_due_date' => $currentDueDate->toDateString(),
                    ]);
                    $currentDueDate->addDays(15);
                }
            }
        }

        // Guardamos la utilidad total calculada para reportes y visualización
        $subscription->profit_amount = $totalProfit;
        $subscription->save();
    }
}