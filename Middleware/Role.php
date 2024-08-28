<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\User;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        try {
            // Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
            if (Auth::check()) {
                // Frissítjük az online státuszt a gyorsítótárban
                $expireTime = Carbon::now()->addSeconds(30);
                Cache::put('user-is-online' . Auth::user()->id, true, $expireTime);

                // Frissítjük a felhasználó utolsó látogatási idejét
                User::where('id', Auth::user()->id)->update([
                    'last_seen' => Carbon::now()
                ]);
            }

            // Ellenőrizzük, hogy a felhasználó szerepe egyezik-e az elvárt szereppel
            if (Auth::check() && $request->user()->role !== $role) {
                // Ha nem egyezik, visszairányítjuk a felhasználót a dashboardra
                return redirect('dashboard')->with('error', 'Nincs jogosultságod a kívánt oldal megtekintéséhez.');
            }

            return $next($request);
        } catch (\Exception $e) {
            // Hiba kezelése
            return redirect('dashboard')->with('error', 'Hiba történt a jogosultság ellenőrzése során.');
        }
    }
}
