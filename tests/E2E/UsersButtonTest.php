<?php

namespace App\Tests\E2E;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UsersButtonTest extends WebTestCase
{
    private Usuario $usuarioNormal;
    private Usuario $usuarioAdmin;

    protected function setUp(): void
    {
        $container = static::getContainer();

        $userRepository = $container->get(UsuarioRepository::class);

        $this->usuarioAdmin = $userRepository->findOneBy([
            'usuario' => 'bechairepaulom'
        ]);

        $this->usuarioNormal = $userRepository->findOneBy([
            'usuario' => 'bechairepaulomn'
        ]);

        $this->tearDown();
    }

    public function testRedirectOfNotAuthenticatedUser(): void
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', 'http://nginx/admin');

        self::assertResponseRedirects('http://nginx/admin/login');
        
    }

    public function testAuthenticatedUserAdmin(): void
    {
        $client = static::createClient();

        $client->loginUser($this->usuarioAdmin);

        $crawler = $client->request('GET', 'http://nginx/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.btn-danger[href="/admin/usuarios"]');
    }

    public function testAuthenticatedUser(): void
    {
        $client = static::createClient();
        
        $client->loginUser($this->usuarioNormal);

        $crawler = $client->request('GET', 'http://nginx/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('.btn-danger');
        self::assertSelectorExists('.btn-success[href="/admin/albuns/new"]');
    }

}

