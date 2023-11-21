<?php

use Illuminate\Support\Facades\Route;

Route::get('/laravel/login', fn() => redirect(route('filament.client.auth.login')))->name('login');
