<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\VendorAprrovedNotification;
use Illuminate\Support\Facades\Notification;

class AdminController extends Controller
{
    // Admin dashboard megjelenítése
    public function AdminDashboard()
    {
        // Bejelentkezési státusz mentése a session-be
        session()->put('user_status', 'belepve');
        // Admin dashboard nézet visszaadása
        return view('admin.index');
    }

    // Admin bejelentkezési oldal megjelenítése
    public function AdminLogin()
    {
        // Admin bejelentkezési nézet visszaadása
        return view('admin.admin_login');
    }

    // Admin kijelentkezése és session törlése
    public function AdminDestroy(Request $request)
    {
        // Session törlése
        session()->forget('user_status');
        // Kijelentkezés
        Auth::guard('web')->logout();
        // Session érvénytelenítése
        $request->session()->invalidate();
        // Session token újragenerálása
        $request->session()->regenerateToken();
        // Átirányítás a bejelentkezési oldalra
        return redirect('/admin/login');
    }

    // Admin profil megjelenítése
    public function AdminProfile()
    {
        // Jelenlegi bejelentkezett admin ID-jának lekérése
        $id = Auth::user()->id;
        // Admin adatok lekérése az adatbázisból
        $admindata = User::find($id);
        // Profil nézet visszaadása
        return view('admin.admin_profile_view', compact('admindata'));
    }

    // Admin profil frissítése
    public function AdminProfileStore(Request $request)
    {
        // Jelenlegi bejelentkezett admin ID-jának lekérése
        $id = Auth::user()->id;
        // Admin adatok lekérése az adatbázisból
        $data = User::find($id);
        // Adatok frissítése
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        // Ha van új fotó, frissítjük azt is
        if ($request->file('photo')) {
            // Régi fotó törlése
            @unlink(public_path('upload/admin_images/' . $data->photo));
            // Új fotó elnevezése és mentése
            $file = $request->file('photo');
            $filename = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'), $filename);
            $data->photo = $filename;
        }

        // Adatok mentése
        $data->save();

        // Sikeres frissítési értesítés
        $notification = array(
            'message' => 'Admin profil sikeresen frissítve',
            'alert-type' => 'success'
        );

        // Visszairányítás a profiloldalra értesítéssel
        return redirect()->back()->with($notification);
    }

    // Admin jelszóváltoztatási oldal megjelenítése
    public function AdminChangePassword()
    {
        return view('admin.admin_change_password');
    }

    // Admin jelszó frissítése
    public function AdminUpdatePassword(Request $request)
    {
        // Kérési validálás
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed', // A new_password confirmation ellenőrzése
        ]);

        // Régi jelszó ellenőrzése
        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return back()->with("error", "A régi jelszó nem stimmel!");
        }

        // Új jelszó frissítése
        User::whereId(Auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Sikeres jelszóváltoztatás értesítés
        return back()->with("status", "Jelszó sikeresen megváltoztatva!");
    }

    // Inaktív eladók listázása
    public function InactiveVendor()
    {
        // Inaktív eladók lekérése
        $inActiveVendor = User::where('status', 'inactive')->where('role', 'vendor')->latest()->get();
        // Inaktív eladók nézet visszaadása
        return view('backend.vendor.inactive_vendor', compact('inActiveVendor'));
    }

    // Inaktív eladó részleteinek megjelenítése
    public function InactiveVendorDetails($id)
    {
        // Inaktív eladó adatainak lekérése
        $inActiveVendorDetails = User::findOrFail($id);
        // Inaktív eladó részletező nézet visszaadása
        return view('backend.vendor.inactive_vendor_details', compact('inActiveVendorDetails'));
    }

    // Aktív eladók listázása
    public function ActiveVendor()
    {
        // Aktív eladók lekérése
        $ActiveVendor = User::where('status', 'active')->where('role', 'vendor')->latest()->get();
        // Aktív eladók nézet visszaadása
        return view('backend.vendor.active_vendor', compact('ActiveVendor'));
    }

    // Eladó aktiválása
    public function ActiveVendorApprove(Request $request)
    {
        // Eladó ID lekérése
        $vendor_id = $request->id;
        // Eladó állapotának frissítése
        User::findOrFail($vendor_id)->update([
            'status' => 'active'
        ]);

        // Sikeres aktiválási értesítés
        $notification = array(
            'message' => 'Üzlet sikeresen aktiválva!',
            'alert-type' => 'success'
        );

        // Értesítés küldése az eladónak
        $vuser = User::where('role', 'vendor')->get();
        Notification::send($vuser, new VendorAprrovedNotification($request));

        // Visszairányítás az aktív eladók oldalára értesítéssel
        return redirect()->route('active.vendor')->with($notification);
    }

    // Aktív eladó részleteinek megjelenítése
    public function ActiveVendorDetails(User $user)
    {
        // Aktív eladó részletező nézet visszaadása a 'user' objektummal
        return view('backend.vendor.active_vendor_details', ['ActiveVendorDetails' => $user]);
    }

    // Eladó inaktiválása
    public function InActiveVendorApprove(Request $request)
    {
        // Eladó ID lekérése
        $vendor_id = $request->id;
        // Eladó állapotának frissítése
        User::findOrFail($vendor_id)->update([
            'status' => 'inactive'
        ]);

        // Sikeres inaktiválási értesítés
        $notification = array(
            'message' => 'Üzlet sikeresen inaktiválva!',
            'alert-type' => 'success'
        );

        // Visszairányítás az inaktív eladók oldalára értesítéssel
        return redirect()->route('inactive.vendor')->with($notification);
    }

    // Adminok listázása
    public function AllAdmin()
    {
        // Adminok lekérése
        $allAdminUser = User::where('role', 'admin')->latest()->get();
        // Adminok nézet visszaadása
        return view('backend.admin.all_admin', compact('allAdminUser'));
    }

    // Új admin hozzáadása oldal megjelenítése
    public function AddAdmin()
    {
        // Szerepkörök lekérése
        $roles = Role::all();
        // Admin hozzáadási nézet visszaadása
        return view('backend.admin.add_admin', compact('roles'));
    }

    // Új admin mentése
public function AdminUserStore(Request $request)
{
    try {
        // Kérési validálás
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'roles' => 'nullable|array'
        ]);

        // Új admin létrehozása
        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->role = 'admin';
        $user->status = 'active';
        $user->save();

        // Szerepkörök hozzárendelése
        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        // Sikeres admin hozzáadás értesítés
        $notification = array(
            'message' => 'Admin sikeresen létrehozva!',
            'alert-type' => 'success'
        );

        // Visszairányítás az adminok listájára értesítéssel
        return redirect()->route('all.admin')->with($notification);

    } catch (\Illuminate\Database\QueryException $e) {
        // Adatbázis lekérdezési hiba kezelése
        $notification = array(
            'message' => 'Hiba történt az admin létrehozása során. Kérjük, próbálja újra!',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    } catch (\Exception $e) {
        // Általános hiba kezelése
        $notification = array(
            'message' => 'Valami hiba történt. Kérjük, próbálja újra!',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }
}


    // Admin szerkesztési oldal megjelenítése
    public function EditAdminRole(User $user)
    {
        // Az összes szerepkör lekérése
        $roles = Role::all();

        // Admin szerkesztési nézet visszaadása a 'user' objektummal
        return view('backend.admin.edit_admin', compact('user', 'roles'));
    }

    // Admin frissítése
    public function AdminUserUpdate(Request $request, $id)
    {

          try {
        // Kérési validálás
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'roles' => 'nullable|array'
        ]);

        // Admin adatok frissítése
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->role = 'admin';
        $user->status = 'active';
        $user->save();

        // Szerepkörök frissítése
        $user->roles()->detach();
        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        // Sikeres admin frissítés értesítés
        $notification = array(
            'message' => 'Admin sikeresen szerkesztve!',
            'alert-type' => 'success'
        );

        // Visszairányítás az adminok listájára értesítéssel
        return redirect()->route('all.admin')->with($notification);
         } catch (\Exception $e) {
        // Általános hiba kezelése
        $notification = array(
            'message' => 'Valami hiba történt. Kérjük, próbálja újra!',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }
    }

    // Admin vagy más felhasználó törlése
    public function DeleteAdminRole($id)
    {
        // Admin törlése
        $user = User::findOrFail($id);
        if (!is_null($user)) {
            $user->delete();
        }

        // Sikeres törlés értesítés
        $notification = array(
            'message' => 'Sikeresen törölve!',
            'alert-type' => 'success'
        );

        // Visszairányítás az adminok listájára értesítéssel
        return redirect()->route('all.admin')->with($notification);
    }
}
