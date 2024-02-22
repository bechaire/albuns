<?php

declare(strict_types=1);

namespace App\Enum;

use App\Trait\EnumToArrayTrait;

enum AlbumStatusEnum: string
{
    use EnumToArrayTrait;
    
    case A = "Ativo";
    case I = "Inativo";
    case C = "Criado";
    case X = "Excluído";
}
