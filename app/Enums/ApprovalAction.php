<?php

namespace App\Enums;

enum ApprovalAction: string
{
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
