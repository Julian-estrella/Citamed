<?php

use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        $defaultRoute = $user->defaultDashboardRoute();

        if ($defaultRoute) {
            return redirect()->route($defaultRoute);
        }

        abort(403, 'No tienes un panel disponible para tu rol actual.');
    })->name('dashboard');

    Route::middleware(['role:administrador', 'permission:visualizar_panel_principal'])->group(function () {
        Route::get('/admin-panel', function () {
            return view('layout.admin.index');
        })->name('admin.panel');

        Route::get('/admin/users/dashboard', [UserManagementController::class, 'dashboard'])->name('admin.users.dashboard');
        Route::get('/admin/users/actions', [UserManagementController::class, 'actions'])->name('admin.users.actions');
        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::patch('/admin/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    });

    Route::middleware(['role:medico', 'permission:consultar_panel_principal_medico'])->group(function () {
        Route::get('/medico', function () {
            return view('layout.medico.index');
        })->name('medico.dashboard');
    });

    Route::middleware(['role:recepcion', 'permission:consultar_agenda'])->group(function () {
        Route::get('/recepcion', function () {
            return view('layout.recepcion.index');
        })->name('recepcion.dashboard');
    });
});
