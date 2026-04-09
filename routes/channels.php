<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('employee.{employeeId}', function ($user, $employeeId) {
    return $user->employee_id === $employeeId;
});

Broadcast::channel('division.{divisionId}', function ($user, $divisionId) {
    return $user->employee?->division_id === $divisionId
        || $user->hasRole(['super_admin', 'hr_admin']);
});

Broadcast::channel('hr', function ($user) {
    return $user->hasRole(['super_admin', 'hr_admin']);
});
