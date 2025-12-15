<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\ActivityLogger;

class UserManagementController extends Controller
{
    public function index()
    {
        // Check if user is owner
        if (!auth()->user()->hasRole('pemilik')) {
            abort(403, 'only business owners can manage users.');
        }

        $business = auth()->user()->currentBusiness;
        $users = $business->users()->with('roles')->get();
        
        return view('users.index', compact('users'));
    }

    public function create()
    {
        // Check if user is owner
        if (!auth()->user()->hasRole('pemilik')) {
            abort(403, 'only business owners can manage users.');
        }

        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // Check if user is owner
        if (!auth()->user()->hasRole('pemilik')) {
            abort(403, 'only business owners can manage users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $business = auth()->user()->currentBusiness;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'current_business_id' => $business->id,
        ]);

        // Attach user to business with role
        $user->businesses()->attach($business->id, ['role_id' => $request->role_id]);

        ActivityLogger::log('created', 'User', $user->id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $request->role_id,
        ], 'Invited user ' . $user->name . ' <' . $user->email . '> as role #' . $request->role_id);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        // Check if user is owner
        if (!auth()->user()->hasRole('pemilik')) {
            abort(403, 'only business owners can manage users.');
        }

        $roles = Role::all();
        $currentRole = $user->roles()
            ->where('role_user.business_id', auth()->user()->current_business_id)
            ->first();
        
        return view('users.edit', compact('user', 'roles', 'currentRole'));
    }

    public function update(Request $request, User $user)
    {
        // Check if user is owner
        if (!auth()->user()->hasRole('pemilik')) {
            abort(403, 'only business owners can manage users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $old = $user->toArray();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update role
        $business = auth()->user()->currentBusiness;
        $user->businesses()->updateExistingPivot($business->id, ['role_id' => $request->role_id]);

        ActivityLogger::log('updated', 'User', $user->id, $old, [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $request->role_id,
        ], 'Edited user ' . $user->name . ' <' . $user->email . '> role change');

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        // Check if user is owner
        if (!auth()->user()->hasRole('pemilik')) {
            abort(403, 'only business owners can manage users.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete yourself!');
        }

        $business = auth()->user()->currentBusiness;
        $old = $user->toArray();
        $user->businesses()->detach($business->id);
        
        // If user has no other businesses, delete the user
        if ($user->businesses()->count() === 0) {
            $user->delete();
        }

        ActivityLogger::log('deleted', 'User', $user->id, $old, null, 'Removed user ' . ($old['name'] ?? 'Unknown') . ' <' . ($old['email'] ?? '') . '>');

        return redirect()->route('users.index')->with('success', 'User removed!');
    }
}
