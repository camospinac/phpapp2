<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class FixClosedPayments extends Command
{
    // Nombre del comando para la terminal
    protected $signature = 'payments:fix-closed {--dry-run : Solo mostrar lo que se cambiaría sin guardar}';

    protected $description = 'Corrige los pagos pendientes de contratos cerrados sumando el capital inicial';

    public function handle()
    {
        $this->info('Iniciando auditoría de pagos para contratos CERRADOS...');

        // 1. Buscamos suscripciones cerradas con pagos pendientes
        $subscriptions = Subscription::where('contract_type', 'cerrada')
            ->whereHas('payments', function($q) {
                $q->where('status', 'pending');
            })
            ->with(['payments' => function($q) {
                $q->where('status', 'pending');
            }])
            ->get();

        $count = 0;

        foreach ($subscriptions as $sub) {
            $payment = $sub->payments->first(); // Los cerrados solo tienen un pago
            
            if (!$payment) continue;

            $capital = (float) $sub->initial_investment;
            $utilidadTotal = (float) $sub->profit_amount;
            $montoCorrecto = $capital + $utilidadTotal;

            // Si el monto actual es diferente al monto correcto (con un margen de error por centavos)
            if (abs($payment->amount - $montoCorrecto) > 0.01) {
                
                $this->warn("Corrigiendo suscripción de: {$sub->user->nombres}");
                $this->line(" - Monto Actual: " . number_format($payment->amount, 2));
                $this->info(" - Monto Nuevo (Capital + Utilidad): " . number_format($montoCorrecto, 2));

                if (!$this->option('dry-run')) {
                    $payment->update(['amount' => $montoCorrecto]);
                }
                
                $count++;
            }
        }

        if ($this->option('dry-run')) {
            $this->info("Simulación terminada. Se habrían corregido {$count} registros.");
        } else {
            $this->info("Mantenimiento finalizado. Se corrigieron {$count} registros exitosamente.");
        }
    }
}