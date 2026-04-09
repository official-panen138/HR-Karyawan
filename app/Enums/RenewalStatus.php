<?php

namespace App\Enums;

enum RenewalStatus: string
{
    case NONE = 'none';
    case IN_PROCESS = 'in_process';
    case DONE = 'done';
}
