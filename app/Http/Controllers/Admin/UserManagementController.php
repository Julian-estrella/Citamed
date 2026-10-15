<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\UniqueClinicPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    private const SUPER_ADMIN_STATUS_ERROR = 'No puedes desactivar al super administrador.';

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
        $filters = $request->validate([
            'role' => ['nullable', 'in:administrador,medico,recepcion'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort' => ['sometimes', 'in:name'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        $users = $query
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $request->search,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'regex:/\A[0-9]{1,8}\z/', new UniqueClinicPhone('users')],
            'password' => [
                'required',
                'string',
                Password::min(8)->max(12)->mixedCase()->numbers()->symbols(),
                'confirmed',
            ],
            'role' => ['required', 'in:administrador,medico,recepcion'],
            'is_active' => ['required', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($user->isMedico()) {
            return redirect()->route('admin.doctors.edit', $user)
                ->with('success', 'Cuenta creada. Completa los datos profesionales del médico.');
        }

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
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'regex:/\A[0-9]{1,8}\z/', new UniqueClinicPhone('users', $user->id)],
            'password' => [
                'nullable',
                'string',
                Password::min(8)->max(12)->mixedCase()->numbers()->symbols(),
                'confirmed',
            ],
            'role' => ['required', 'in:administrador,medico,recepcion'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->isSuperAdmin() && ! (bool) $validated['is_active']) {
            return redirect()->route('admin.users')->with('error', self::SUPER_ADMIN_STATUS_ERROR);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->role = $validated['role'];
        $user->is_active = (bool) $validated['is_active'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->isSuperAdmin() && $user->isActive()) {
            return redirect()->route('admin.users')->with('error', self::SUPER_ADMIN_STATUS_ERROR);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Estado del usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.users')->with('error', 'No puedes eliminar al super administrador.');
        }

        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Usuario eliminado correctamente.');
    }
}
