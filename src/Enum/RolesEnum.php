<?php

declare(strict_types=1);

namespace App\Enum;

use App\Trait\EnumToArrayTrait;

enum RolesEnum: string
{
    use EnumToArrayTrait;

    case ROLE_USER = 'Usuário padrão';
    case ROLE_ADMIN = 'Administrador';
}
