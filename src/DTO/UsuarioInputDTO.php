<?php

namespace App\DTO;

use App\Enum\RolesEnum;
use Symfony\Component\Validator\Constraints as Assert;

class UsuarioInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 5, max: 65,  minMessage: 'Nome de USUÁRIO muito curto', maxMessage: 'Nome de USUÁRIO muito longo')]
        public string $usuario = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 5, max: 65,  minMessage: 'Valor para NOME muito curto', maxMessage: 'Valor para NOME muito longo')]
        public string $nome = '',

        #[Assert\NotBlank]
        #[Assert\Email(message: 'O campo E-MAIL precisa conter um valor válido', )]
        #[Assert\Length(min: 15, max: 120,  minMessage: 'Campo EMAIL muito curto', maxMessage: 'Campo EMAIL muito longo')]
        public string $email = '',

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [RolesEnum::class, 'getNames'], message: 'Valor inválido para o campo PAPEL {{value}}')]
        public string $papel = '',

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['S', 'N'], message: 'Valor inválido para o campo ATIVO')]
        public string $ativo = '',
    ) {
    }
}
