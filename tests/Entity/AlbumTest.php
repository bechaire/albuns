<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use DateTime;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function testCreateNewAlbum(): void
    {
        $album = new Album('CFJL', new DateTime, 'Novo ábum do CFJL');

        self::assertEquals($album->getTitulo(), 'Novo ábum do CFJL');
    }
}
