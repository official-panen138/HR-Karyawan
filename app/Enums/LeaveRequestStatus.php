<?php

namespace App\Enums;

enum LeaveRequestStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PARTIALLY_APPROVED = 'partially_approved';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
