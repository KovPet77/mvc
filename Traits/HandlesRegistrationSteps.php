<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisztracioMegerosites;
use App\Models\User;

trait HandlesRegistrationSteps
{
    public function RegisztracioMasodikLepes(Request $request)
    {
        try {
            // Kérési validálás
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'adatkezeles' => 'required|boolean',
            ]);

            // Véletlen 4 számjegyű kód generálása és munkamenetben tárolása
            $verificationCode = rand(1000, 9999);
            Session::put('verification_code', $verificationCode);
            Session::put('name', $request->name);
            Session::put('username', $request->username);
            Session::put('email', $request->email);
            Session::put('adatkezeles', $request->adatkezeles);
            Session::put('password', Hash::make($request->password));

            // Munkamenet tartalmának ellenőrzése
            if (Session::get('adatkezeles') === null) {
                return view('frontend/users/invalid_adatok');
            }

            // Email küldése
            $mail = new RegisztracioMegerosites($verificationCode);
            Mail::to(Session::get('email'))->send($mail);

            return view('frontend.users.RegisztracioMasodikLepes');
        } catch (\Exception $e) {
            // Hiba kezelése
            return redirect()->back()->with('error', 'Hiba történt a regisztráció során. Kérjük, próbálja újra.');
        }
    }

    public function RegisztracioHarmadiklepes(Request $request)
    {
        try {
            // Kérési validálás
            $request->validate([
                'confirmation_code' => 'required|integer',
            ]);

            // A post requestből kinyert confirmation_code
            $confirmationCode = $request->confirmation_code;

            // A Session-ből kinyert verification_code
            $verificationCode = Session::get('verification_code');

            // Ellenőrzés, hogy a két kód egyezik-e
            if ($confirmationCode == $verificationCode) {
               
                $user = new User();
                $user->name = Session::get('name');
                $user->username = Session::get('username');
                $user->email = Session::get('email');
                $user->password = Session::get('password');
                $user->save();

                // Session-ben tárolt adatok törlése
                Session::forget(['verification_code', 'name', 'username', 'email', 'password']);

                // Sikeres regisztráció visszajelzése
                return view('frontend.users.RegisztracioSiker');
            } else {
                // A kódok nem egyeznek
                return view('frontend.users.RegisztracioSikertelen');
            }
        } catch (\Exception $e) {
            // Hiba kezelése
            return redirect()->back()->with('error', 'Hiba történt a regisztráció megerősítése során. Kérjük, próbálja újra.');
        }
    }
}
