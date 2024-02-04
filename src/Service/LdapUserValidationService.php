<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\InvalidCredentialsException;

class LdapUserValidationService
{
    public function __construct(
        private ParameterBagInterface $params,
        private LoggerInterface $log
    ) {
    }

    public function isValid(string $ldapUser, #[\SensitiveParameter] string $ldapRawPass): bool
    {
        $ldap = Ldap::create('ext_ldap', [
            'host' => $this->params->get('app.ldap.host')
        ]);

        try {
            $ldap->bind($this->params->get('app.ldap.usrprefix') . $ldapUser, $ldapRawPass);
            return true;
        } catch (InvalidCredentialsException) {
            $this->log->warning('Falha ao validar a senha no LDAP', [
                'Usuário' => $ldapUser,
                'IP' => $_SERVER['REMOTE_ADDR'],
                'Path' => $_SERVER['REQUEST_URI'],
            ]);
        } catch (ConnectionException) {
            $this->log->critical('Erro de conexão ao LDAP', [
                'Usuário' => $ldapUser,
                'IP' => $_SERVER['REMOTE_ADDR'],
                'Path' => $_SERVER['REQUEST_URI'],
            ]);
        } catch (\Exception $e) {
            $this->log->alert('Erro desconhecido ao validar Usuário e Senha no LDAP', [
                'Usuário' => $ldapUser,
                'IP' => $_SERVER['REMOTE_ADDR'],
                'Path' => $_SERVER['REQUEST_URI'],
            ]);
        }

        return false;
    }
}
