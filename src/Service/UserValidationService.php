<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\InvalidCredentialsException;

class UserValidationService
{
    public function __construct(
        private ParameterBagInterface $params,
        private LoggerInterface $log,
        private UsuarioRepository $usuarioRepository
    ) {
    }

    public function validateUserPassword(?string $user, #[\SensitiveParameter] ?string $password): Usuario
    {
        $usuarioOuSenhaEmBranco = empty($user) || empty($password);

        if ($usuarioOuSenhaEmBranco) {
            throw new \DomainException('Usuário ou senha inválidos');
        }

        $usuario = $this->usuarioRepository->findOneBy([
            'usuario' => $user,
            'ativo' => 'S'
        ]);

        if (!$usuario) {
            $this->log->warning('Tentativa de logon com usuário não cadastrado previamente ou desativado', [$user]);
            throw new \DomainException('Usuário ou senha inválidos');
        }

        $usuarioLocalizado = $usuario->getUsuario();
        $usuarioValido = $this->validateLDAPPassword($usuarioLocalizado, $password);

        if (!$usuarioValido) {
            $this->log->warning('Tentativa de logon com usuário localizado, mas senha incorreta', [$user]);
            throw new \DomainException('Usuário ou senha inválidos');
        }

        $this->log->info('Sucesso ao validar usuário e senha', $this->userAccessInfo($user));

        return $usuario;
    }

    public function validateLDAPPassword(string $ldapUser, #[\SensitiveParameter] string $ldapRawPass): bool
    {
        $ldap = Ldap::create('ext_ldap', [
            'host' => $this->params->get('app.ldap.host')
        ]);

        try {
            $ldap->bind($this->params->get('app.ldap.usrprefix') . $ldapUser, $ldapRawPass);
            return true;
        } catch (InvalidCredentialsException) {
            $this->log->warning('Falha ao validar a senha no LDAP', $this->userAccessInfo($ldapUser));
        } catch (ConnectionException) {
            $this->log->critical('Erro de conexão ao LDAP', $this->userAccessInfo($ldapUser));
        } catch (\Exception $e) {
            $this->log->alert('Erro desconhecido ao validar Usuário e Senha no LDAP', $this->userAccessInfo($ldapUser));
        }

        return false;
    }

    private function userAccessInfo(string $username): array
    {
        return [
            'Usuário' => $username,
            'IP' => $_SERVER['REMOTE_ADDR'] ?? null,
            'Path' => $_SERVER['REQUEST_URI'] ?? null,
        ];
    }
}
