<?php

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

        if ($user && $user->isAdmin()) {
            return view('layout.admin.index');
        }

        if ($user && $user->isMedico()) {
            return view('layout.medico.index');
        }

        return view('layout.recepcion.index');
    })->name('dashboard');

    Route::middleware('role:administrador')->get('/admin-panel', function () {
        return view('layout.admin.index');
    })->name('admin.panel');

    Route::middleware('role:administrador')->get('/admin/users', function () {
        return view('layout.admin.users.index');
    })->name('admin.users');

    Route::middleware('role:medico')->get('/medico', function () {
        return view('layout.medico.index');
    })->name('medico.dashboard');

    Route::middleware('role:recepcion')->get('/recepcion', function () {
        return view('layout.recepcion.index');
    })->name('recepcion.dashboard');
});
