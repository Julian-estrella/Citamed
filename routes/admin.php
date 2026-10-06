<?php

use illuminate\support\Facades\Route;

Route:: get('/', function () {
    return view('admin.index');
})->name('index');