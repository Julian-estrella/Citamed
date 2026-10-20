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
    'active',
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

        Route::get('/admin/users/dashboard', [UserManagementController::class, 'dashboard'])->middleware('permission:gestionar_usuarios')->name('admin.users.dashboard');
        Route::get('/admin/users/actions', [UserManagementController::class, 'actions'])->middleware('permission:gestionar_usuarios')->name('admin.users.actions');
        Route::get('/admin/users', [UserManagementController::class, 'index'])->middleware('permission:gestionar_usuarios')->name('admin.users');
        Route::get('/admin/users/create', [UserManagementController::class, 'create'])->middleware('permission:gestionar_usuarios')->name('admin.users.create');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->middleware('permission:gestionar_usuarios')->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->middleware('permission:gestionar_usuarios')->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->middleware('permission:gestionar_usuarios')->name('admin.users.update');
        Route::patch('/admin/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->middleware('permission:gestionar_usuarios')->name('admin.users.toggle-status');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->middleware('permission:gestionar_usuarios')->name('admin.users.destroy');

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
        Route::get('/admin/medicos/create', [PanelSectionController::class, 'createDoctor'])
            ->middleware('permission:gestionar_medicos')->name('admin.doctors.create');
        Route::post('/admin/medicos', [PanelSectionController::class, 'storeDoctor'])
            ->middleware('permission:gestionar_medicos')->name('admin.doctors.store');
        Route::get('/admin/medicos/{doctor}/edit', [PanelSectionController::class, 'editDoctor'])
            ->middleware('permission:gestionar_medicos')->name('admin.doctors.edit');
        Route::put('/admin/medicos/{doctor}', [PanelSectionController::class, 'updateDoctor'])
            ->middleware('permission:gestionar_medicos')->name('admin.doctors.update');
        Route::patch('/admin/medicos/{doctor}/toggle-status', [PanelSectionController::class, 'toggleDoctorStatus'])
            ->middleware('permission:gestionar_medicos')->name('admin.doctors.toggle-status');
        Route::delete('/admin/medicos/{doctor}', [PanelSectionController::class, 'destroyDoctor'])
            ->middleware('permission:gestionar_medicos')->name('admin.doctors.destroy');
        Route::get('/admin/consultorios', [PanelSectionController::class, 'consultationRooms'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios');
        Route::get('/admin/consultorios/create', [PanelSectionController::class, 'createConsultationRoom'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios.create');
        Route::post('/admin/consultorios', [PanelSectionController::class, 'storeConsultationRoom'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios.store');
        Route::get('/admin/consultorios/{room}/edit', [PanelSectionController::class, 'editConsultationRoom'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios.edit');
        Route::put('/admin/consultorios/{room}', [PanelSectionController::class, 'updateConsultationRoom'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios.update');
        Route::patch('/admin/consultorios/{room}/toggle-status', [PanelSectionController::class, 'toggleConsultationRoom'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios.toggle-status');
        Route::delete('/admin/consultorios/{room}', [PanelSectionController::class, 'destroyConsultationRoom'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios.destroy');
        Route::get('/admin/consultorios/{room}/disponibilidad', [PanelSectionController::class, 'consultationRoomAvailability'])
            ->middleware('permission:gestionar_medicos')->name('admin.consultorios.availability');
        Route::get('/admin/citas', [PanelSectionController::class, 'appointments'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments');
        Route::get('/admin/citas/horarios-disponibles', [PanelSectionController::class, 'appointmentSlots'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.slots');
        Route::get('/admin/citas/create', [PanelSectionController::class, 'createAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.create');
        Route::post('/admin/citas', [PanelSectionController::class, 'storeAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.store');
        Route::get('/admin/citas/{appointment}/edit', [PanelSectionController::class, 'editAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.edit');
        Route::put('/admin/citas/{appointment}', [PanelSectionController::class, 'updateAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.update');
        Route::patch('/admin/citas/{appointment}/cancel', [PanelSectionController::class, 'cancelAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.cancel');
        Route::patch('/admin/citas/{appointment}/confirm', [PanelSectionController::class, 'confirmAppointment'])
            ->middleware('permission:gestionar_citas')->name('admin.appointments.confirm');
        Route::get('/admin/agenda', [PanelSectionController::class, 'agenda'])
            ->middleware('permission:consultar_agenda')->name('admin.agenda');
        Route::get('/admin/agenda/{date}', [PanelSectionController::class, 'agendaDay'])
            ->where('date', '\\d{4}-\\d{2}-\\d{2}')
            ->middleware('permission:consultar_agenda')->name('admin.agenda.day');
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
    Route::get('/medico/citas/horarios-disponibles', [PanelSectionController::class, 'appointmentSlots'])
        ->middleware(['role:medico', 'permission:programar_citas'])->name('medico.appointments.slots');
    Route::get('/medico/citas/create', [PanelSectionController::class, 'createAppointment'])
        ->middleware(['role:medico', 'permission:programar_citas'])->name('medico.appointments.create');
    Route::post('/medico/citas', [PanelSectionController::class, 'storeAppointment'])
        ->middleware(['role:medico', 'permission:programar_citas'])->name('medico.appointments.store');
    Route::get('/medico/citas/{appointment}/edit', [PanelSectionController::class, 'editAppointment'])
        ->middleware(['role:medico', 'permission:modificar_citas'])->name('medico.appointments.edit');
    Route::put('/medico/citas/{appointment}', [PanelSectionController::class, 'updateAppointment'])
        ->middleware(['role:medico', 'permission:modificar_citas'])->name('medico.appointments.update');
    Route::patch('/medico/citas/{appointment}/cancel', [PanelSectionController::class, 'cancelAppointment'])
        ->middleware(['role:medico', 'permission:cancelar_citas'])->name('medico.appointments.cancel');
    Route::patch('/medico/citas/{appointment}/confirm', [PanelSectionController::class, 'confirmAppointment'])
        ->middleware(['role:medico', 'permission:modificar_citas'])->name('medico.appointments.confirm');
    Route::get('/medico/agenda', [PanelSectionController::class, 'agenda'])
        ->middleware(['role:medico', 'permission:gestionar_horario_atencion'])->name('medico.agenda');
    Route::get('/medico/agenda/{date}', [PanelSectionController::class, 'agendaDay'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->middleware(['role:medico', 'permission:gestionar_horario_atencion'])->name('medico.agenda.day');
    Route::get('/medico/disponibilidad', [PanelSectionController::class, 'availability'])
        ->middleware(['role:medico', 'permission:gestionar_disponibilidad'])->name('medico.availability');
    Route::get('/medico/disponibilidad/{date}', [PanelSectionController::class, 'agendaDay'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->middleware(['role:medico', 'permission:gestionar_disponibilidad'])->name('medico.availability.day');

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
    Route::get('/recepcion/medicos/create', [PanelSectionController::class, 'createDoctor'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.doctors.create');
    Route::post('/recepcion/medicos', [PanelSectionController::class, 'storeDoctor'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.doctors.store');
    Route::get('/recepcion/medicos/{doctor}/edit', [PanelSectionController::class, 'editDoctor'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.doctors.edit');
    Route::put('/recepcion/medicos/{doctor}', [PanelSectionController::class, 'updateDoctor'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.doctors.update');
    Route::patch('/recepcion/medicos/{doctor}/toggle-status', [PanelSectionController::class, 'toggleDoctorStatus'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.doctors.toggle-status');
    Route::delete('/recepcion/medicos/{doctor}', [PanelSectionController::class, 'destroyDoctor'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.doctors.destroy');
    Route::get('/recepcion/consultorios', [PanelSectionController::class, 'consultationRooms'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios');
    Route::get('/recepcion/consultorios/create', [PanelSectionController::class, 'createConsultationRoom'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios.create');
    Route::post('/recepcion/consultorios', [PanelSectionController::class, 'storeConsultationRoom'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios.store');
    Route::get('/recepcion/consultorios/{room}/edit', [PanelSectionController::class, 'editConsultationRoom'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios.edit');
    Route::put('/recepcion/consultorios/{room}', [PanelSectionController::class, 'updateConsultationRoom'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios.update');
    Route::patch('/recepcion/consultorios/{room}/toggle-status', [PanelSectionController::class, 'toggleConsultationRoom'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios.toggle-status');
    Route::delete('/recepcion/consultorios/{room}', [PanelSectionController::class, 'destroyConsultationRoom'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios.destroy');
    Route::get('/recepcion/consultorios/{room}/disponibilidad', [PanelSectionController::class, 'consultationRoomAvailability'])
        ->middleware(['role:recepcion', 'permission:gestionar_medicos'])->name('recepcion.consultorios.availability');
    Route::get('/recepcion/citas', [PanelSectionController::class, 'appointments'])
        ->middleware(['role:recepcion', 'permission:gestionar_citas'])->name('recepcion.appointments');
    Route::get('/recepcion/citas/horarios-disponibles', [PanelSectionController::class, 'appointmentSlots'])
        ->middleware(['role:recepcion', 'permission:gestionar_citas'])->name('recepcion.appointments.slots');
    Route::get('/recepcion/citas/create', [PanelSectionController::class, 'createAppointment'])
        ->middleware(['role:recepcion', 'permission:programar_citas'])->name('recepcion.appointments.create');
    Route::post('/recepcion/citas', [PanelSectionController::class, 'storeAppointment'])
        ->middleware(['role:recepcion', 'permission:programar_citas'])->name('recepcion.appointments.store');
    Route::get('/recepcion/citas/{appointment}/edit', [PanelSectionController::class, 'editAppointment'])
        ->middleware(['role:recepcion', 'permission:modificar_citas'])->name('recepcion.appointments.edit');
    Route::put('/recepcion/citas/{appointment}', [PanelSectionController::class, 'updateAppointment'])
        ->middleware(['role:recepcion', 'permission:modificar_citas'])->name('recepcion.appointments.update');
    Route::patch('/recepcion/citas/{appointment}/cancel', [PanelSectionController::class, 'cancelAppointment'])
        ->middleware(['role:recepcion', 'permission:cancelar_citas'])->name('recepcion.appointments.cancel');
    Route::patch('/recepcion/citas/{appointment}/confirm', [PanelSectionController::class, 'confirmAppointment'])
        ->middleware(['role:recepcion', 'permission:modificar_citas'])->name('recepcion.appointments.confirm');
    Route::get('/recepcion/agenda', [PanelSectionController::class, 'agenda'])
        ->middleware(['role:recepcion', 'permission:consultar_agenda'])->name('recepcion.agenda');
    Route::get('/recepcion/agenda/{date}', [PanelSectionController::class, 'agendaDay'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->middleware(['role:recepcion', 'permission:consultar_agenda'])->name('recepcion.agenda.day');
});
