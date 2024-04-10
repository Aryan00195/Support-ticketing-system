<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/userlogin', [AuthController::class, 'userLogin']);
Route::get('/callback', [AuthController::class, 'callback']);
Route::get('/authuser', [AuthController::class, 'authUser']);
