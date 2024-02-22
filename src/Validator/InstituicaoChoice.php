<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class InstituicaoChoice extends Constraint
{
    public string $message = 'Valor inválido para o campo INSTITUIÇÃO';
}
