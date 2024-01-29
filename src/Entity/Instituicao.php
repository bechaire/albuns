<?php

namespace App\Entity;

use App\Repository\InstituicaoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstituicaoRepository::class)]
#[ORM\Table(name:'instituicoes')]
class Instituicao
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idinstituicao')]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 10)]
        public readonly string $sigla,
    
        #[ORM\Column(length: 45)]
        public readonly string $nome,
    
        #[ORM\Column(length: 15)]
        public readonly string $telefone,
    
        #[ORM\Column(length: 75)]
        public readonly string $endereco,
    
        #[ORM\Column(length: 150)]
        public readonly string $palavraschave,
    
        #[ORM\Column(length: 255)]
        public readonly string $descricao
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
