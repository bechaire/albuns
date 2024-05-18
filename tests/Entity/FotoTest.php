<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Entity\Foto;
use PHPUnit\Framework\TestCase;

class FotoTest extends TestCase
{
    private Foto $foto;

    protected function setUp(): void
    {
        $album = new Album('INSTITUICAO', new \DateTime, 'Título do álbum');

        $this->foto = new Foto($album);
    }

    public function testValidaOpcoesExtraDaFoto(): void
    {
        $jsonDefault = '{"fliph":0,"flipv":0,"rotate":0}';
        $jsonValido1 = '{"fliph":1,"flipv":0,"rotate":180}';
        $jsonValido2 = '{"rotate":180,"flipv":0,"fliph":1}';
        $jsonInvalido1 = '{"fliph":1,"flipj":0,"rotate":180}';
        $jsonInvalido2 = '{"fliph":1,"rotate":180}';
        $jsonInvalido3 = '{d}';
        
        $this->foto->setOpcoes($jsonValido1);
        self::assertEquals($jsonValido1, $this->foto->getOpcoes());
        $this->foto->setOpcoes($jsonValido2);
        self::assertEquals($jsonValido1, $this->foto->getOpcoes());

        $this->foto->setOpcoes($jsonInvalido1);
        self::assertEquals($jsonDefault, $this->foto->getOpcoes());
        $this->foto->setOpcoes($jsonInvalido2);
        self::assertEquals($jsonDefault, $this->foto->getOpcoes());
        $this->foto->setOpcoes($jsonInvalido3);
        self::assertEquals($jsonDefault, $this->foto->getOpcoes());
    }

    public function testValidaDefinicoesPadrao(): void
    {
        self::assertEquals('S', $this->foto->getVisivel());
        self::assertEquals('N', $this->foto->getDestaque());
    }

    public function testValidacaoTipoValorCampoVisivel(): void
    {
        self::expectException(\DomainException::class);
        self::expectExceptionMessage(
            'O campo VISIVEL precisa ter um valor válido ("S" ou "N")'
        );
        $this->foto->setVisivel('P');
    }

    public function testValidacaoTipoValorCampoDestaque(): void
    {
        self::expectException(\DomainException::class);
        self::expectExceptionMessage(
            'O campo DESTAQUE precisa ter um valor válido ("S" ou "N")'
        );
        $this->foto->setDestaque('P');
    }
}