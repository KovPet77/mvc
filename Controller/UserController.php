<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisztracioMegerosites;

class UserController extends Controller
{
    public function Regisztracio()
    {
        return view('auth/regisztracio');
    }

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

    public function UserDashboard()
    {
        try {
            $id = Auth::user()->id; // users table id-ja
            $userData = User::findOrFail($id);

            return view('index', compact('userData')); // userData adatok átpasszolása az index oldalnak
        } catch (\Exception $e) {
            // Hiba kezelése
            return redirect()->back()->with('error', 'Hiba történt a felhasználói adatok lekérése során.');
        }
    }

    public function UserProfileStore(Request $request)
    {
        try {
            // Kérési validálás
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'phone' => 'nullable|string|max:15',
                'address' => 'nullable|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $id = Auth::user()->id;
            $data = User::findOrFail($id);

            $data->name = $request->name;
            $data->username = $request->username;
            $data->email = $request->email;
            $data->phone = $request->phone;
            $data->address = $request->address;

            if ($request->file('photo')) {
                $file = $request->file('photo');
                @unlink(public_path('upload/user_images/' . $data->photo));
                $filename = date('YmdHi') . $file->getClientOriginalName();
                $file->move(public_path('upload/user_images'), $filename);
                $data->photo = $filename;
            }

            $data->save();

            // Sikeres frissítés értesítés
            $notification = array(
                'message' => 'Felhasználó profil sikeresen frissítve',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            // Hiba kezelése
            return redirect()->back()->with('error', 'Hiba történt a profil frissítése során.');
        }
    }

    public function UserLogout(Request $request)
    {
        try {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $notification = array(
                'message' => 'Sikeres kijelentkezés',
                'alert-type' => 'success'
            );

            return redirect('/login')->with($notification);
        } catch (\Exception $e) {
            // Hiba kezelése
            return redirect()->back()->with('error', 'Hiba történt a kijelentkezés során.');
        }
    }

    public function UserUpdatePassword(Request $request)
    {
        try {
            // Kérési validálás
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // Régi jelszó ellenőrzése
            if (!Hash::check($request->old_password, Auth::user()->password)) {
                return back()->with("error", "A régi jelszó nem stimmel!");
            }

            User::whereId(Auth()->user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);

            return back()->with("status", "Jelszó sikeresen megváltoztatva!");
        } catch (\Exception $e) {
            // Hiba kezelése
            return back()->with("error", "Hiba történt a jelszó frissítése során.");
        }
    }
}
