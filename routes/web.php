<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PanelSectionController;
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
        Route::get('/admin-panel', [DashboardController::class, 'admin'])->name('admin.panel');

        Route::get('/admin/users/dashboard', [UserManagementController::class, 'dashboard'])->name('admin.users.dashboard');
        Route::get('/admin/users/actions', [UserManagementController::class, 'actions'])->name('admin.users.actions');
        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::patch('/admin/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/admin/pacientes', [PanelSectionController::class, 'patients'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients');
        Route::get('/admin/pacientes/create', [PanelSectionController::class, 'createPatient'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients.create');
        Route::post('/admin/pacientes', [PanelSectionController::class, 'storePatient'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients.store');
        Route::get('/admin/pacientes/{patient}', [PanelSectionController::class, 'showPatient'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients.show');
        Route::get('/admin/pacientes/{patient}/edit', [PanelSectionController::class, 'editPatient'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients.edit');
        Route::put('/admin/pacientes/{patient}', [PanelSectionController::class, 'updatePatient'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients.update');
        Route::patch('/admin/pacientes/{patient}/toggle-status', [PanelSectionController::class, 'togglePatient'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients.toggle-status');
        Route::delete('/admin/pacientes/{patient}', [PanelSectionController::class, 'destroyPatient'])
            ->middleware('permission:gestionar_pacientes')->name('admin.patients.destroy');
        Route::get('/admin/medicos', [PanelSectionController::class, 'doctors'])
            ->middleware('permission:gestionar_medicos')->name('admin.doctors');
        Route::get('/admin/citas', [PanelSectionController::class, 'appointments'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments');
        Route::post('/admin/citas', [PanelSectionController::class, 'storeAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.store');
        Route::patch('/admin/citas/{appointment}/cancel', [PanelSectionController::class, 'cancelAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.cancel');
        Route::get('/admin/agenda', [PanelSectionController::class, 'agenda'])
            ->middleware('permission:consultar_agenda')->name('admin.agenda');
    });

    Route::middleware(['role:medico', 'permission:consultar_panel_principal_medico'])->group(function () {
        Route::get('/medico', [DashboardController::class, 'doctor'])->name('medico.dashboard');
    });

    Route::middleware(['role:recepcion', 'permission:consultar_agenda'])->group(function () {
        Route::get('/recepcion', [DashboardController::class, 'reception'])->name('recepcion.dashboard');
    });

    Route::get('/medico/pacientes', [PanelSectionController::class, 'patients'])
        ->middleware(['role:medico', 'permission:consultar_pacientes_que_atiende'])->name('medico.patients');
    Route::get('/medico/pacientes/{patient}', [PanelSectionController::class, 'showPatient'])
        ->middleware(['role:medico', 'permission:consultar_pacientes_que_atiende'])->name('medico.patients.show');
    Route::get('/medico/citas', [PanelSectionController::class, 'appointments'])
        ->middleware(['role:medico', 'permission:consultar_citas'])->name('medico.appointments');
    Route::get('/medico/agenda', [PanelSectionController::class, 'agenda'])
        ->middleware(['role:medico', 'permission:gestionar_horario_atencion'])->name('medico.agenda');
    Route::get('/medico/disponibilidad', [PanelSectionController::class, 'availability'])
        ->middleware(['role:medico', 'permission:gestionar_disponibilidad'])->name('medico.availability');

    Route::get('/recepcion/pacientes', [PanelSectionController::class, 'patients'])
        ->middleware(['role:recepcion', 'permission:consultar_pacientes'])->name('recepcion.patients');
    Route::get('/recepcion/pacientes/create', [PanelSectionController::class, 'createPatient'])
        ->middleware(['role:recepcion', 'permission:registrar_pacientes'])->name('recepcion.patients.create');
    Route::post('/recepcion/pacientes', [PanelSectionController::class, 'storePatient'])
        ->middleware(['role:recepcion', 'permission:registrar_pacientes'])->name('recepcion.patients.store');
    Route::get('/recepcion/pacientes/{patient}', [PanelSectionController::class, 'showPatient'])
        ->middleware(['role:recepcion', 'permission:consultar_pacientes'])->name('recepcion.patients.show');
    Route::get('/recepcion/pacientes/{patient}/edit', [PanelSectionController::class, 'editPatient'])
        ->middleware(['role:recepcion', 'permission:modificar_pacientes'])->name('recepcion.patients.edit');
    Route::put('/recepcion/pacientes/{patient}', [PanelSectionController::class, 'updatePatient'])
        ->middleware(['role:recepcion', 'permission:modificar_pacientes'])->name('recepcion.patients.update');
    Route::patch('/recepcion/pacientes/{patient}/toggle-status', [PanelSectionController::class, 'togglePatient'])
        ->middleware(['role:recepcion', 'permission:modificar_pacientes'])->name('recepcion.patients.toggle-status');
    Route::get('/recepcion/medicos', [PanelSectionController::class, 'doctors'])
        ->middleware(['role:recepcion', 'permission:consultar_horarios_disponibles_medicos'])->name('recepcion.doctors');
    Route::get('/recepcion/citas', [PanelSectionController::class, 'appointments'])
        ->middleware(['role:recepcion', 'permission:gestionar_citas'])->name('recepcion.appointments');
    Route::post('/recepcion/citas', [PanelSectionController::class, 'storeAppointment'])
        ->middleware(['role:recepcion', 'permission:programar_citas'])->name('recepcion.appointments.store');
    Route::patch('/recepcion/citas/{appointment}/cancel', [PanelSectionController::class, 'cancelAppointment'])
        ->middleware(['role:recepcion', 'permission:cancelar_citas'])->name('recepcion.appointments.cancel');
    Route::get('/recepcion/agenda', [PanelSectionController::class, 'agenda'])
        ->middleware(['role:recepcion', 'permission:consultar_agenda'])->name('recepcion.agenda');
});
