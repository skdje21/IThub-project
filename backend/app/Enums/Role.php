<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Employee = 'employee';
    case Manager = 'manager';
    case Admin = 'admin';
}
