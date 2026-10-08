<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function dashboard()
    {
        $users = User::latest()->get();

        return view('admin.users.dashboard', [
            'users' => $users,
        ]);
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $request->search,
        ]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:50', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:8', 'confirmed'],
            'role' => ['required', 'in:administrador,medico,recepcion'],
            'is_active' => ['required', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('admin.users')->with('success', 'Usuario registrado correctamente.');
    }

    public function actions()
    {
        return view('admin.users.actions');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:administrador,medico,recepcion'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->is_active = (bool) ($validated['is_active'] ?? $user->is_active);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleStatus(User $user)
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Estado del usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Usuario eliminado correctamente.');
    }
}
