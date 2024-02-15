<?php

declare(strict_types=1);

namespace App\Enum;

enum AlbumStatusEnum: string
{
    case A = "Ativo";
    case I = "Inativo";
    case C = "Criado";
    case X = "Excluído";
}
