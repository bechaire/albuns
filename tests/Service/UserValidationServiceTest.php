<?php

namespace App\Tests\Service;

use App\Repository\UsuarioRepository;
use App\Service\UserValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserValidationServiceTest extends KernelTestCase
{
    public function testValidateValidUserFound(): void
    {
        $container = static::getContainer();
        $parameters = $container->get(ParameterBagInterface::class);
        $userRepository = $container->get(UsuarioRepository::class);
        $logger = $container->get(LoggerInterface::class);

        $usrValidationService = $this->getMockBuilder(UserValidationService::class)
                                     ->onlyMethods(['validateLDAPPassword'])
                                     ->setConstructorArgs([$parameters, $logger, $userRepository])
                                     ->getMock();
        $usrValidationService->method('validateLDAPPassword')
                             ->withAnyParameters()
                             ->willReturn(true);
        
        /** @var UserValidationService $usrValidationService */
        $result = $usrValidationService->validateUserPassword('bechairepaulom', '12345');
        
        static::assertEquals('bechairepaulom', $result->getUsuario());
    }

    public function testValidateUserNotFound(): void
    {
        self::expectException(\DomainException::class);
        self::expectExceptionMessage(
            'Usuário ou senha inválidos'
        );

        $container = static::getContainer();
        $parameters = $container->get(ParameterBagInterface::class);
        $userRepository = $container->get(UsuarioRepository::class);
        $logger = $container->get(LoggerInterface::class);

        $usrValidationService = $this->getMockBuilder(UserValidationService::class)
                                     ->onlyMethods(['validateLDAPPassword'])
                                     ->setConstructorArgs([$parameters, $logger, $userRepository])
                                     ->getMock();
        $usrValidationService->method('validateLDAPPassword')
                             ->withAnyParameters()
                             ->willReturn(true);
        
        /** @var UserValidationService $usrValidationService */
        $result = $usrValidationService->validateUserPassword('bechairepaulomx', '12345');
        
    }

}
