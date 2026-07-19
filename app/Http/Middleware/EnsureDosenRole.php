<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDosenRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && $request->session()->get('dosen_farmasi.role_selected') === false) {
            return redirect()->route('role.select');
        }

        if (! $user || ! $user->is_active || ($roles !== [] && ! in_array($user->role, $roles, true))) {
            abort(403);
        }

        return $next($request);
    }
}
