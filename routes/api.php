<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BreakQuotaController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\FeatureFlagController;
use App\Http\Controllers\Api\IpWhitelistController;
use App\Http\Controllers\Api\LeaveApprovalController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

// Public — rate limited
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Telegram webhook — no auth, verified by secret header
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

// Protected — rate limited
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/stats', [ReportController::class, 'dashboard']);

    // Employees
    Route::apiResource('employees', EmployeeController::class);

    // Documents
    Route::apiResource('documents', DocumentController::class);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);

    // Shifts
    Route::apiResource('shifts', ShiftController::class);
    Route::post('/shifts/assign', [ShiftController::class, 'assign']);
    Route::get('/shift-assignments', [ShiftController::class, 'assignments']);
    Route::delete('/shift-assignments/{employeeShift}', [ShiftController::class, 'removeAssignment']);

    // Break Quotas
    Route::apiResource('break-quotas', BreakQuotaController::class)->except(['show']);

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('office_ip');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('office_ip');
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])->middleware('office_ip');
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])->middleware('office_ip');
    Route::get('/attendance/report', [AttendanceController::class, 'report']);

    // IP Whitelist
    Route::apiResource('ip-whitelist', IpWhitelistController::class)->except(['show']);

    // Leave
    Route::get('/leave/types', [LeaveController::class, 'types']);
    Route::get('/leave/balances', [LeaveController::class, 'balances']);
    Route::get('/leave/calculate-days', [LeaveController::class, 'calculateDays']);
    Route::apiResource('leave-requests', LeaveController::class)->except(['update']);
    Route::post('/leave-requests/{leaveRequest}/cancel', [LeaveController::class, 'cancel']);

    // Leave Approvals
    Route::get('/leave-approvals/pending', [LeaveApprovalController::class, 'pending']);
    Route::post('/leave-approvals/{approval}/approve', [LeaveApprovalController::class, 'approve']);
    Route::post('/leave-approvals/{approval}/reject', [LeaveApprovalController::class, 'reject']);

    // Feature Flags
    Route::apiResource('feature-flags', FeatureFlagController::class)->except(['show']);
    Route::post('/feature-flags/{featureFlag}/toggle', [FeatureFlagController::class, 'toggle']);

    // Reports
    Route::get('/reports/attendance', [ReportController::class, 'attendance']);
    Route::get('/reports/leave', [ReportController::class, 'leave']);
    Route::get('/reports/documents', [ReportController::class, 'documents']);

    // Export CSV
    Route::prefix('export')->group(function () {
        Route::get('/attendance', [ExportController::class, 'attendance']);
        Route::get('/leave', [ExportController::class, 'leave']);
        Route::get('/documents', [ExportController::class, 'documents']);
    });
});
