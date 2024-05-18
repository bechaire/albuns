<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\Table(name:'usuarios')]
#[ORM\HasLifecycleCallbacks]
class Usuario implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idusuario')]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Album::class, mappedBy: 'usuario')]
    private Collection $albuns;

    #[ORM\Column(length: 65, unique: true)]
    private ?string $usuario;

    #[ORM\Column(length: 120, unique: true)]
    private ?string $email;

    #[ORM\Column(length: 65)]
    private ?string $nome;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 1)]
    private ?string $ativo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ultacesso = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated = null;

    public function __construct(string $usuario, string $email, string $nome) {
        $this->albuns = new ArrayCollection();

        $this->setUsuario($usuario);
        $this->setEmail($email);
        $this->setNome($nome);
        $this->setAtivo('S');
        $this->setRoles(['ROLE_USER']);

        $this->setCreated(new \DateTimeImmutable());
        if ($this->getUpdated() === null) {
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function eraseCredentials(): void { }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string { 
        return $this->usuario;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Album>
     */
    public function getAlbuns(): Collection
    {
        return $this->albuns;
    }

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario): static
    {
        if (strpos($usuario, '@')) {
            throw new \DomainException('O usuário não pode ser um endereço de e-mail');
        }

        $this->usuario = mb_strtolower($usuario);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = trim(mb_strtolower($email));

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('E-mail inválido para o usuário');
        }

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = trim($nome);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * Deve ser um array com um ou mais papéis do usuário, sabendo que ROLE_USER sempre será adicionado ao usuário
     *
     * @param array $roles ['ROLE_ADMIN'] ou ['ROLE_ADMIN', 'ROLE_USER', 'ROLE_XYZ']
     * @return static
     */
    public function setRoles(array $roles): static
    {
        $roles[] = 'ROLE_USER';

        $this->roles = array_unique($roles);

        return $this;
    }

    public function getAtivo(): string
    {
        return $this->ativo;
    }

    public function setAtivo(string $ativo): static
    {
        if (!in_array($ativo, ['S', 'N'])) {
            throw new \DomainException('O campo ATIVO precisa ter um valor válido ("S" ou "N")');
        }

        $this->ativo = $ativo;

        return $this;
    }

    public function getUltacesso(): ?\DateTimeInterface
    {
        return $this->ultacesso;
    }

    public function setUltacesso(\DateTimeInterface $ultacesso): static
    {
        $this->ultacesso = $ultacesso;

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
