<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:administrador',
])->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    })->name('index');
});