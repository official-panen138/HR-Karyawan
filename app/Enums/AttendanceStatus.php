<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case ABSENT = 'absent';
    case PERMISSION = 'permission';
    case DAY_OFF = 'day_off';
}
