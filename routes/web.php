<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'can:access admin'])
    ->group(__DIR__.'/admin.php');
