<?php

declare(strict_types=1);

namespace App\Enums;

enum EquipmentStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Repair = 'repair';
    case Decommissioned = 'decommissioned';
}
