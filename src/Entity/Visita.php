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

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'visitas')]
    #[ORM\JoinColumn(name: 'idalbum', referencedColumnName:'idalbum')]
    private ?Album $album;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datahora = null;

    #[ORM\Column(length: 255)]
    private ?string $userinfo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }

    public function getDatahora(): ?\DateTimeInterface
    {
        return $this->datahora;
    }

    public function setDatahora(\DateTimeInterface $datahora): static
    {
        $this->datahora = $datahora;

        return $this;
    }

    public function getUserinfo(): ?string
    {
        return $this->userinfo;
    }

    public function setUserinfo(string $userinfo): static
    {
        $this->userinfo = $userinfo;

        return $this;
    }
}
