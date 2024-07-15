<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {

        if ($role == 'admin' && auth()->user()->user_type != User::APPLICANT) {
            abort(403);
        }

        if ($role == 'employer' && auth()->user()->user_type != USER::EMPLOYER) {
            abort(403);
        }

        if ($role == 'applicant' && auth()->user()->user_type != USER::APPLICANT) {
            abort(403);
        }

        return $next($request);
    }
}