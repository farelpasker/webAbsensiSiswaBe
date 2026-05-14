<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
});

Route::middleware(['auth:sanctum','role:admin','handle.role.auth'])->group(function () {
    //list User
    Route::get('/users', [AuthController::class, 'listUsers']);

    //student
    Route::post('/students', [StudentController::class, 'store']);
    Route::put('/students/{student}', [StudentController::class, 'update']);
    Route::delete('/students/{student}', [StudentController::class, 'destroy']);
    //parent
    Route::apiResource('/parents', ParentController::class)->except(['index','show']);
    //teacher
    Route::apiResource('/teachers', TeacherController::class);
    Route::post('/teachers/{teacher}/assign-classroom', [TeacherController::class, 'assignClassroom']);
    //kelas
    Route::apiResource('/kelas', KelasController::class);
    //holiday
    Route::apiResource('/holidays', HolidayController::class);
});

Route::middleware(['auth:sanctum','role:student','handle.role.auth'])->group(function () {
    //attendance
    Route::post('/absen', [AttendanceController::class, 'absen']);
    Route::get('/absen/me', [AttendanceController::class, 'me']);
    Route::get('/absen/me/today', [AttendanceController::class, 'today']);
    Route::get('/absen/me/calender', [AttendanceController::class, 'calender']);
    //leave request
    Route::get('/students/leave-requests', [LeaveRequestController::class, 'myLeaveRequests']);
    Route::post('/students/leave-requests', [LeaveRequestController::class, 'store']);
    //student
    Route::post('/students/register-face', [StudentController::class, 'registerFace']);
    Route::get('/students/me/face', [StudentController::class, 'myFace']);
});

Route::middleware(['auth:sanctum','role:teacher|admin','handle.role.auth'])->group(function () {
    //student
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{student}', [StudentController::class, 'show']);
    //parent
    Route::get('/parents', [ParentController::class, 'index']);
    Route::get('/parents/{parent}', [ParentController::class, 'show']);
    //leave request
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::get('/leave-requests/{id}', [LeaveRequestController::class, 'show']);
    Route::put('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::put('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
});


