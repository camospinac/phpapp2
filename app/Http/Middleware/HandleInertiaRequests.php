<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\Rank;
use App\Models\Campaign;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
 public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        // --- INICIA LA LÓGICA AÑADIDA ---
        $user = $request->user();
        $nextRank = null;
        $userWithRankData = null;

        if ($user) {
            // Buscamos el siguiente rango/objetivo del usuario
            $nextRank = Rank::where('required_referrals', '>', $user->referral_count)
                            ->orderBy('required_referrals', 'asc')
                            ->first();

            // Cargamos la relación del rango actual para que esté disponible
            $user->load('rank');

            // Creamos un array específico con los datos que la vista necesita
            $userWithRankData = [
                'id' => $user->id,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'email' => $user->email,
                'rol' => $user->rol,
                'referral_code' => $user->referral_code,
                'rank' => $user->rank,
                'referral_count' => $user->referral_count,
                'next_rank' => $nextRank,
            ];
        }
        // --- FIN DE LA LÓGICA AÑADIDA ---

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                // Reemplazamos el user() simple por nuestro array enriquecido
                'user' => $userWithRankData,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'withdrawal_code' => fn () => $request->session()->get('withdrawal_code'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'active_campaign' => fn () => Campaign::where('is_active', true)->first(),
        ];
    }
}
