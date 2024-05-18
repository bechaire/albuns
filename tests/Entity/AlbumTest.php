<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Enum\AlbumStatusEnum;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    private Album $album;

    protected function setUp(): void
    {
        $this->album = new Album('INSTITUICAO ', new \DateTime, '   Título do álbum ');
    }

    public function testCreateNewAlbum(): void
    {
        self::assertEquals($this->album->getInstituicao(), 'INSTITUICAO');
        self::assertEquals($this->album->getTitulo(), 'Título do álbum');
        self::assertEquals($this->album->getAno(), (new \DateTimeImmutable())->format('Y'));
        self::assertNull($this->album->getLocal());
    }
    
    public function testThrowExceptionOnInvalidAlbumStatus(): void
    {
        $validStatusList = AlbumStatusEnum::getNames();

        self::expectException(\DomainException::class);
        self::expectExceptionMessage(
            'O campo STATUS precisa ter um valor válido: ' . implode(', ', $validStatusList)
        );

        $this->album->setStatus('P');
    }

    public function testeAcceptOnlyZeroOrPositiveValuesOnFlags(): void
    {
        $this->album->setAcessos(0);
        self::assertEquals($this->album->getAcessos(), 0);
        $this->album->setAcessos(66);
        self::assertEquals($this->album->getAcessos(), 66);
        $this->album->setAcessos(-7);
        self::assertEquals($this->album->getAcessos(), 0);
    }
}
