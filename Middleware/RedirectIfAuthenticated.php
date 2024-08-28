<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[]  ...$guards
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Ha nincsenek megadva őrzők, akkor az alapértelmezett őrzőt használjuk
        $guards = empty($guards) ? [null] : $guards;

        try {
            foreach ($guards as $guard) {
                // Ellenőrizzük, hogy a felhasználó be van-e jelentkezve az adott őrzővel
                if (Auth::guard($guard)->check()) {
                    // Ellenőrizzük a felhasználó szerepét és irányítjuk a megfelelő oldalra
                    switch (Auth::user()->role) {
                        case 'user':
                            return redirect('/dashboard');
                        case 'vendor':
                            return redirect('/vendor/dashboard');
                        case 'admin':
                            return redirect('/admin/dashboard');
                        default:
                            // Ha a szerep nem ismert, visszairányítunk egy alapértelmezett oldalra
                            return redirect('/home');
                    }
                }
            }
        } catch (\Exception $e) {
            // Hiba esetén visszairányítunk egy hibaoldalra vagy az alapértelmezett oldalra
            return redirect('/home')->with('error', 'Hiba történt az autentikálás során.');
        }

        // Ha a felhasználó nincs bejelentkezve, folytatjuk a kérés feldolgozását
        return $next($request);
    }
}
