<?php

namespace App\Entity;

use App\Repository\VisitaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitaRepository::class)]
#[ORM\Table(name:'visitas')]
class Visita
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idvisita')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public readonly \DateTimeInterface $datahora;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'visitas')]
        #[ORM\JoinColumn(name: 'idalbum', referencedColumnName:'idalbum')]
        public readonly Album $album,

        #[ORM\Column(length: 255)]
        public readonly string $userinfo,
    ) {
        $this->datahora = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

}
