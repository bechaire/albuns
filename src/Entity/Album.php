<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
#[ORM\Table(name:'albuns')]
#[ORM\HasLifecycleCallbacks]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idalbum')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'albuns')]
    #[ORM\JoinColumn(name: 'idusuario', referencedColumnName:'idusuario')]
    private ?Usuario $usuario = null;

    #[ORM\OneToMany(targetEntity: Visita::class, mappedBy: 'album')]
    private Collection $visitas;

    #[ORM\OneToMany(targetEntity: Foto::class, mappedBy: 'album')]
    private Collection $fotos;

    #[ORM\Column(length: 1, options: ['comment'=>'A = Ativo | E = Em Espera | I  = Inativo (Remover futuramente)'])]
    private ?string $status = null;

    #[ORM\Column(length: 4)]
    private ?string $ano = null;

    #[ORM\Column(length: 1)]
    private ?string $addtag = null;

    #[ORM\Column(nullable: true)]
    private ?int $acessos = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated = null;

    public function __construct(
        #[ORM\Column(length: 10)]
        private ?string $instituicao,
    
        #[ORM\Column(type: Types::DATE_MUTABLE)]
        private ?\DateTimeInterface $data,

        #[ORM\Column(length: 255, nullable: true)]
        private ?string $local,
    ) {
        $this->visitas = new ArrayCollection();
        $this->fotos = new ArrayCollection();

        $this->setAcessos(0);
        $this->setAddtag('S');
        $this->setStatus('E');

        $this->setCreated(new \DateTimeImmutable());
        if ($this->getUpdated() === null) {
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    /**
     * @return Collection<int, Visita>
     */
    public function getVisitas(): Collection
    {
        return $this->visitas;
    }

    /**
     * @return Collection<int, Visita>
     */
    public function getFotos(): Collection
    {
        return $this->fotos;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getInstituicao(): ?string
    {
        return $this->instituicao;
    }

    public function setInstituicao(string $instituicao): static
    {
        $this->instituicao = $instituicao;

        return $this;
    }

    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(\DateTimeInterface $data): static
    {
        $this->data = $data;
        $this->ano = $data->format('Y');
        return $this;
    }

    public function getAno(): ?string
    {
        return $this->ano;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(?string $local): static
    {
        $this->local = $local;

        return $this;
    }

    public function getAddtag(): ?string
    {
        return $this->addtag;
    }

    public function setAddtag(string $addtag): static
    {
        $this->addtag = $addtag;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAcessos(): ?int
    {
        return $this->acessos;
    }

    public function setAcessos(?int $acessos): static
    {
        $this->acessos = $acessos;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): static
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