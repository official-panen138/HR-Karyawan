<?php

namespace App\Enums;

enum DocType: string
{
    case KTP = 'ktp';
    case PASSPORT = 'passport';
    case WORKING_PERMIT = 'working_permit';
    case VISA = 'visa';
}
