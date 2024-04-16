<?php

namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsuarioFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $usuario = new Usuario('bechairepaulom', 'bechairepauloX@cfjl.com.br', 'Paulo BechaireX');
        
        $manager->persist($usuario);

        $manager->flush();
    }
}
