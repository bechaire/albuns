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

    #[ORM\Column(length: 10)]
    private ?string $sigla = null;

    #[ORM\Column(length: 45)]
    private ?string $nome = null;

    #[ORM\Column(length: 15)]
    private ?string $telefone = null;

    #[ORM\Column(length: 75)]
    private ?string $endereco = null;

    #[ORM\Column(length: 150)]
    private ?string $palavraschave = null;

    #[ORM\Column(length: 255)]
    private ?string $descricao = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSigla(): ?string
    {
        return $this->sigla;
    }

    public function setSigla(string $sigla): static
    {
        $this->sigla = $sigla;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function setTelefone(string $telefone): static
    {
        $this->telefone = $telefone;

        return $this;
    }

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function setEndereco(string $endereco): static
    {
        $this->endereco = $endereco;

        return $this;
    }

    public function getPalavraschave(): ?string
    {
        return $this->palavraschave;
    }

    public function setPalavraschave(string $palavraschave): static
    {
        $this->palavraschave = $palavraschave;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): static
    {
        $this->descricao = $descricao;

        return $this;
    }
}
