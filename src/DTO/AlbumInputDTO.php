<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\AlbumStatusEnum;
use App\Validator\InstituicaoChoice;
use Symfony\Component\Validator\Constraints as Assert;

class AlbumInputDTO
{
    public function __construct(
        #[InstituicaoChoice]
        public string $instituicao = '',

        #[Assert\NotBlank(message: 'O campo TÍTULO não pode ficar vazio')]
        #[Assert\Length(min: 5, max: 350,  minMessage: 'Campo TÍTULO muito curto', maxMessage: 'Campo TÍTULO muito longo')]
        public string $titulo = '',

        #[Assert\NotBlank(message: 'O campo DATA não pode ficar vazio')]
        #[Assert\GreaterThanOrEqual('1929-03-01', message: 'A data precisa ser superior à fundação do CFJL')]
        #[Assert\LessThanOrEqual('today', message: 'Não informe datas futuras')]
        public ?\DateTime $data = null,

        #[Assert\Length(min: 5, max: 255,  minMessage: 'Campo LOCAL muito curto', maxMessage: 'Campo LOCAL muito longo')]
        public ?string $local = '',

        #[Assert\NotBlank(message: 'O campo STATUS não pode ficar vazio')]
        #[Assert\Choice(callback: [AlbumStatusEnum::class, 'getNames'], message: 'Valor inválido para o campo STATUS')]
        public string $status = '',

        #[Assert\NotBlank(message: 'O campo TAG não pode ficar vazio')]
        #[Assert\Choice(choices: ['S', 'N'], message: 'Valor inválido para o campo TAG')]
        public string $addtag = '',

        public string $criador = '',

        public ?\DateTime $created = null,
    ) {
    }
}
