<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessDuePayments extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     */
    protected $signature = 'payments:process-due';

    /**
     * La descripción del comando de consola.
     */
    protected $description = 'Procesa los pagos vencidos y los convierte en transacciones de abono';

    /**
     * Ejecuta la lógica del comando.
     */
    public function handle()
    {
        $this->info('Iniciando procesamiento de pagos vencidos...');

        // 1. Buscamos todos los pagos que están pendientes y cuya fecha de pago es hoy o ya pasó.
        $duePayments = Payment::where('status', 'pending')
                            ->whereDate('payment_due_date', '<=', Carbon::today())
                            ->get();

        if ($duePayments->isEmpty()) {
            $this->info('No hay pagos vencidos para procesar.');
            return;
        }

        $this->info($duePayments->count() . ' pagos encontrados. Procesando...');

        foreach ($duePayments as $payment) {
            // Usamos una transacción para asegurar la integridad de los datos
            DB::transaction(function () use ($payment) {
                // 2. Creamos el registro de 'abono' en la tabla de transacciones
                Transaction::create([
                    'id_user' => $payment->subscription->user_id,
                    'tipo' => 'abono',
                    'monto' => $payment->amount,
                    'observacion' => "Abono automático de cuota #{$payment->id} de la suscripción #{$payment->subscription_id}",
                ]);

                // 3. Actualizamos el estado del pago para no volver a procesarlo
                $payment->status = 'accredited'; // Un nuevo estado: 'acreditado'
                $payment->save();
            });
        }

        $this->info('¡Procesamiento completado con éxito!');
    }
}