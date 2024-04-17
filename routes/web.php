<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/userlogin', [AuthController::class, 'userLogin']);
Route::get('/callback', [AuthController::class, 'callback']);
Route::get('/authuser', [AuthController::class, 'authUser']);
Route::post('/checkuser', [LoginController::class, 'check'])->name('checkuser')->middleware('guest');
Route::get('/logout', [LoginController::class, 'logout']);
Route::group(["prefix" => "/admin", "middleware" => "auth"], function () {
    Route::get('/panel', function () {
        return view('admin.adminpanel');
    });
    Route::get('/users', function () {
        return view('admin.user');
    });
    Route::get('/get/users', [AdminController::class, 'getUsers']);
    Route::get('/find/user/{userId}', [AdminController::class, 'findUser']);
    Route::post('/add/user', [AdminController::class, 'addUsers']);
    Route::post('/update/user/{userId}', [AdminController::class, 'updateUser']);
    Route::delete('/remove/user/{userId}', [AdminController::class, 'removeUsers']);
    //tickets 
    Route::get('/ticket', function () {
        return view('admin.tickets');
    });
    Route::get('/get/tickets', [TicketController::class, 'index']);
    //profile
    Route::get('/profile', function () {
        return view('admin.profile');
    });
});
Route::get('/user/ticket', function () {
    return view('user.userpanel');
});
