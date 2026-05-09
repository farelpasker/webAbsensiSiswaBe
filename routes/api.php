<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    //student
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
});

Route::middleware(['auth:sanctum','role:student'])->group(function () {
    //auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    //attendance
    Route::post('/absen', [AttendanceController::class, 'absen']);
    Route::get('/absen/me', [AttendanceController::class, 'me']);
    Route::get('/absen/me/today', [AttendanceController::class, 'today']);
    Route::get('/absen/me/calender', [AttendanceController::class, 'calender']);
    //student
    Route::post('/students/register-face', [StudentController::class, 'registerFace']);
    Route::get('/students/me/face', [StudentController::class, 'myFace']);
    Route::post('/students/verify-face', [StudentController::class, 'verifyFace']);
    //leave request
    Route::post('/students/leave-requests', [LeaveRequestController::class, 'store']);
});
