<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Public routes
Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('login', [UserController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:api')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('admin', [UserController::class, 'adminDashboard'])->middleware('admin')->name('admin');
    Route::get('manager', [UserController::class, 'managerDashboard'])->middleware('manager')->name('manager');
    Route::get('employee', [UserController::class, 'employeeDashboard'])->middleware('employee')->name('employee');
});

