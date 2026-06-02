<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GateOfficerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('gate.login');
        }

        if (! $user->canAccessGateDashboard()) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
