<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    /**
     * Muestra una lista de todas las suscripciones pendientes de verificación.
     */
    public function pending()
    {
        $pendingSubscriptions = Subscription::with(['user', 'plan'])
            ->where('status', 'pending_verification')
            ->latest()
            ->get();

        return Inertia::render('Admin/Subscriptions/Pending', [
            'subscriptions' => $pendingSubscriptions,
        ]);
    }

    /**
     * Aprueba una suscripción, cambiando su estado a 'active'.
     */
    public function approve(Subscription $subscription)
    {
        // Validamos que la suscripción esté realmente pendiente
        if ($subscription->status !== 'pending_verification') {
            return back()->with('error', 'Esta suscripción no se puede aprobar.');
        }

        $subscription->status = 'active';
        $subscription->save();

        // (Futuro) Aquí podrías enviar un email al usuario notificándole.

        return redirect()->route('admin.subscriptions.pending')
            ->with('success', '¡Suscripción aprobada con éxito!');
    }
}