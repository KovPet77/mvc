<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    public function AllPermission()
    {
        $permissions = Permission::all();
        return view('backend.pages.permission.all_permission', compact('permissions'));
    }

    public function AddPermission()
    {
        return view('backend.pages.permission.add_permission');
    }

    public function StorePermission(Request $request)
    {
        // Bemeneti adatok validálása
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'group_name' => 'required|string|max:255',
        ]);

        // Jogosultság létrehozása
        Permission::create($validatedData);

        // Értesítés a sikeres hozzáadásról
        return redirect()->route('all.permission')->with([
            'message' => 'Jogosultság sikeresen hozzáadva!',
            'alert-type' => 'success',
        ]);
    }

    public function EditPermission(Permission $permission)
    {
        return view('backend.pages.permission.edit_permission', compact('permission'));
    }

    public function UpdatePermission(Request $request, Permission $permission)
    {
        // Bemeneti adatok validálása
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'group_name' => 'required|string|max:255',
        ]);

        // Jogosultság frissítése
        $permission->update($validatedData);

        // Értesítés a sikeres frissítésről
        return redirect()->route('all.permission')->with([
            'message' => 'Jogosultság sikeresen szerkesztve!',
            'alert-type' => 'success',
        ]);
    }

    public function DeletePermission(Permission $permission)
    {
        $permission->delete();

        // Értesítés a sikeres törlésről
        return redirect()->back()->with([
            'message' => 'Jogosultság sikeresen törölve!',
            'alert-type' => 'success',
        ]);
    }

    public function AllRoles()
    {
        $roles = Role::all();
        return view('backend.pages.roles.all_roles', compact('roles'));
    }

    public function AddRoles()
    {
        return view('backend.pages.roles.add_roles');
    }

    public function StoreRoles(Request $request)
    {
        // Bemeneti adatok validálása
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        // Szerep létrehozása
        Role::create($validatedData);

        // Értesítés a sikeres hozzáadásról
        return redirect()->route('all.roles')->with([
            'message' => 'Szerep sikeresen hozzáadva!',
            'alert-type' => 'success',
        ]);
    }

    public function EditRoles(Role $role)
    {
        return view('backend.pages.roles.edit_roles', compact('role'));
    }

    public function UpdateRoles(Request $request, Role $role)
    {
        // Bemeneti adatok validálása
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        // Szerep frissítése
        $role->update($validatedData);

        // Értesítés a sikeres frissítésről
        return redirect()->route('all.roles')->with([
            'message' => 'Szerep sikeresen szerkesztve!',
            'alert-type' => 'success',
        ]);
    }

    public function DeleteRoles(Role $role)
    {
        $role->delete();

        // Értesítés a sikeres törlésről
        return redirect()->back()->with([
            'message' => 'Szerep sikeresen törölve!',
            'alert-type' => 'success',
        ]);
    }

    public function AddRolesPermission()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $permission_group = User::getPermissionGroups();
        return view('backend.pages.roles.add_roles_permission', compact('roles', 'permissions', 'permission_group'));
    }

    public function RolePermissionStore(Request $request)
    {
        // Bemeneti adatok validálása
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id',
        ]);

        // Jogosultságok hozzáadása szerephez
        $role = Role::findOrFail($validatedData['role_id']);
        foreach ($validatedData['permission'] as $permissionId) {
            $role->givePermissionTo($permissionId);
        }

        // Értesítés a sikeres hozzáadásról
        return redirect()->route('all_roles_permission')->with([
            'message' => 'Szerep jogosultság hozzáadva!',
            'alert-type' => 'success',
        ]);
    }

    public function AllRolesPermission()
    {
        $roles = Role::all();
        return view('backend.pages.roles.all_roles_permission', compact('roles'));
    }

    public function AdminRolesEdit(Role $role)
    {
        $permissions = Permission::all();
        $permission_groups = User::getPermissionGroups();
        return view('backend.pages.roles.role_permission_edit', compact('role', 'permissions', 'permission_groups'));
    }

    public function AdminRolesUpdate(Request $request, Role $role)
    {
        // Bemeneti adatok validálása
        $validatedData = $request->validate([
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id',
        ]);

        // Jogosultságok szinkronizálása
        $role->syncPermissions($validatedData['permission']);

        // Értesítés a sikeres frissítésről
        return redirect()->route('all.roles.permission')->with([
            'message' => 'Szerep jogosultság sikeresen frissítve!',
            'alert-type' => 'success',
        ]);
    }

    public function AdminRolesDelete(Role $role)
    {
        $role->delete();

        // Értesítés a sikeres törlésről
        return redirect()->back()->with([
            'message' => 'Szerep jogosultság sikeresen törölve!',
            'alert-type' => 'success',
        ]);
    }
}
