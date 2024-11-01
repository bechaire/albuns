<?php

namespace App\Tests\Repository;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumRepositoryTest extends KernelTestCase
{
    public function testRegisterNewAlbum(): void
    {
        $kernel = self::bootKernel();

        $container = static::getContainer();
        
        /** @var AlbumRepository $albumRepository */
        $albumRepository = $container->get(AlbumRepository::class);

        $albumRepository->add(new Album('CFJL ', new \DateTime, 'Album de teste do PHP unit'), true);

        $albuns = $albumRepository->findAll();

        self::assertCount(1, $albuns);
        self::assertEquals('CFJL', $albuns[0]->getInstituicao());
    }
}
