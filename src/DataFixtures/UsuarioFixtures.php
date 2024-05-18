<?php

namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsuarioFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $usuario = new Usuario('bechairepaulom', 'bechairepaulo@cfjl.com.br', 'Paulo Bechaire');
        $manager->persist($usuario);
        $usuario->setRoles(['ROLE_ADMIN']);

        $usuarioN = new Usuario('bechairepaulomn', 'bechairepauloN@cfjl.com.br', 'Paulo BechaireN');        
        $manager->persist($usuarioN);

        $manager->flush();
    }
}
