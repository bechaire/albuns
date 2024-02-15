<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FotoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FotoRepository::class)]
#[ORM\Table(name:'fotos')]
#[ORM\HasLifecycleCallbacks]
class Foto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idfoto')]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $arquivo = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $youtubeid = null;

    #[ORM\Column(length: 1)]
    private ?string $visivel = null;

    #[ORM\Column(length: 1)]
    private ?string $destaque = null;

    #[ORM\Column]
    private ?int $ordem = null;

    #[ORM\Column(length: 15)]
    private ?string $identificador = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'fotos')]
        #[ORM\JoinColumn(name: 'idalbum', referencedColumnName:'idalbum')]
        private ?Album $album
    ) {
        $this->setDestaque('N');
        $this->setVisivel('S');

        $this->setCreated(new \DateTimeImmutable());
        if ($this->getUpdated() === null) {
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function getArquivo(): ?string
    {
        return $this->arquivo;
    }

    public function setArquivo(?string $arquivo): static
    {
        $this->arquivo = $arquivo;

        return $this;
    }

    public function getYoutubeid(): ?string
    {
        return $this->youtubeid;
    }

    public function setYoutubeid(?string $youtubeid): static
    {
        $this->youtubeid = $youtubeid;

        return $this;
    }

    public function getVisivel(): ?string
    {
        return $this->visivel;
    }

    public function setVisivel(string $visivel): static
    {
        $this->visivel = $visivel;

        return $this;
    }

    public function getDestaque(): ?string
    {
        return $this->destaque;
    }

    public function setDestaque(string $destaque): static
    {
        $this->destaque = $destaque;

        return $this;
    }

    public function getOrdem(): ?int
    {
        return $this->ordem;
    }

    public function setOrdem(int $ordem): static
    {
        $this->ordem = $ordem;

        return $this;
    }

    public function getIdentificador(): ?string
    {
        return $this->identificador;
    }

    public function setIdentificador(string $identificador): static
    {
        $this->identificador = $identificador;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    private function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    private function setUpdated(\DateTimeInterface $updated): static
    {
        $this->updated = $updated;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateModifiedDatetime(): void
    {
        $this->setUpdated(new \DateTimeImmutable());
    }

}
