<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanIsSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If the user is an admin, let them pass.
        if (!$user || $user->rol === 'admin') {
            return $next($request);
        }

        // If the user has a plan, but is trying to access the selection page,
        // redirect them to the dashboard.
        if ($user->subscriptions()->exists() && $request->routeIs('plan.selection')) {
            return redirect()->route('dashboard');
        }

        // In all other cases, let the request continue.
        return $next($request);
    }
}
