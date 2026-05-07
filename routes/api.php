<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum','role:student'])->group(function () {
    Route::post('/absen', [AttendanceController::class, 'absen']);
    Route::get('/absen/me', [AttendanceController::class, 'me']);
    Route::get('/absen/me/today', [AttendanceController::class, 'today']);
    Route::post('/students/register-face', [StudentController::class, 'registerFace']);
    Route::get('/students/me/face', [StudentController::class, 'myFace']);
});