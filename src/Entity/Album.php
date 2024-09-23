<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\AlbumStatusEnum;
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

    #[ORM\Column(length: 10)]
    private ?string $instituicao = null;

    #[ORM\Column(length: 350)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $local = null;

    //\App\Enum\AlbumStatusEnum
    #[ORM\Column(length: 1, options: ['comment'=>'A = Ativo | I  = Inativo | C = Criado | X = Excluído'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $data;

    #[ORM\Column(length: 4)]
    private ?string $ano = null;

    #[ORM\Column(length: 1)]
    private ?string $addtag = null;

    #[ORM\Column(nullable: true)]
    private ?int $acessos = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $destaque = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated = null;

    public function __construct(
        string $instituicao,
        \DateTimeInterface $data,
        string $titulo,
        ?string $local = null,
    ) {
        $this->visitas = new ArrayCollection();
        $this->fotos = new ArrayCollection();

        $this->setInstituicao($instituicao);
        $this->setData($data);
        $this->setTitulo($titulo);
        $this->setLocal($local);
        $this->setAcessos(0);
        $this->setAddtag('S');
        $this->setStatus('C');

        $this->setCreated(new \DateTimeImmutable());
        if ($this->getUpdated() === null) {
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsuario(): Usuario
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
     * @return Collection<int, Foto>
     */
    public function getFotos(): Collection
    {
        return $this->fotos;
    }

    public function setUsuario(Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getInstituicao(): string
    {
        return $this->instituicao;
    }

    public function setInstituicao(string $instituicao): static
    {
        $this->instituicao = trim($instituicao);

        return $this;
    }

    public function getData(): \DateTimeInterface
    {
        return $this->data;
    }

    public function setData(\DateTimeInterface $data): static
    {
        $this->data = $data;
        $this->ano = $data->format('Y');
        return $this;
    }

    public function getAno(): string
    {
        return $this->ano;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = trim($titulo);

        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(?string $local): static
    {
        if (!is_null($local)) {
            $local = trim($local);
        }

        $this->local = $local;

        return $this;
    }

    public function getAddtag(): string
    {
        return $this->addtag;
    }

    public function setAddtag(string $addtag): static
    {
        if (!in_array($addtag, ['S', 'N'])) {
            throw new \DomainException('O campo ADDTAG precisa ter um valor válido ("S" ou "N")');
        }
        $this->addtag = $addtag;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $status = strtoupper($status);
        $validStatusList = AlbumStatusEnum::getNames();

        if (!in_array($status, $validStatusList)) {
            throw new \DomainException('O campo STATUS precisa ter um valor válido: ' . implode(', ', $validStatusList));
        }

        $this->status = $status;

        return $this;
    }

    public function getAcessos(): int
    {
        return $this->acessos;
    }

    public function setAcessos(int $acessos): static
    {
        if ($acessos < 0) {
            $acessos = 0;
        }
        $this->acessos = $acessos;

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
