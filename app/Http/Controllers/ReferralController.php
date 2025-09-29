<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class ReferralController extends Controller
{
    /**
     * Muestra la lista de usuarios referidos por el usuario autenticado.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Buscamos a todos los usuarios cuyo 'referred_by_id' sea el nuestro.
        // 2. Usamos with() para cargar eficientemente la primera suscripción de cada referido
        //    y el plan asociado a esa suscripción.
        $referrals = User::where('referred_by_id', $user->id)
            ->with(['subscriptions' => function ($query) {
                $query->latest()->limit(1)->with('plan'); // Obtenemos la suscripción más reciente
            }])
            ->get();

        return Inertia::render('Referrals/Index', [
            'referrals' => $referrals,
        ]);
    }
}