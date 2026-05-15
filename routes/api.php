<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\SettingController;
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
    //rekap absensi
    Route::get('/absen/admin', [AttendanceController::class, 'index']);
    Route::get('/absen/recap', [AttendanceController::class, 'recap']);
    //reset face descriptor
    Route::post('/students/{student}/reset-face', [StudentController::class, 'resetFaceDescriptor']);
    //settings
    Route::apiResource('/settings', SettingController::class);
    Route::post('/settings/initialize-defaults', [SettingController::class, 'initializeDefaults']);
    //export excel
    Route::get('/absen/export', [AttendanceController::class, 'exportExcelByAdmin']);
    Route::get('/absen/export-recap', [AttendanceController::class, 'exportRecapExcelByAdmin']);
    Route::get('/students/export', [StudentController::class, 'export']);
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
    //settings - get attendance settings
    Route::get('/settings/attendance', [SettingController::class, 'getAttendanceSettings']);
});

Route::middleware(['auth:sanctum','role:teacher','handle.role.auth'])->group(function () {
    //attendance
    Route::get('/absen/teacher', [AttendanceController::class, 'listByTeacher']);
    Route::get('/absen/teacher/recap/{kelasId}', [AttendanceController::class, 'recapClassByTeacher']);
    Route::get('/absen/teacher/export-recap/{kelasId}', [AttendanceController::class, 'exportRecapClassByTeacher']);
    //kelas
    Route::get('/my/classes', [KelasController::class, 'myClasses']);

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


