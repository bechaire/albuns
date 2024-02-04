<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:user-add',
    description: 'Cria um novo usuário',
    hidden: false,
    aliases: ['app:add-user']
)]
class UserAddCommand extends Command
{
    public function __construct(
        private UsuarioRepository $userRepository
    ) {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $output->writeln([
            'Criador de usuários',
            '==================================================================',
            '',
        ]);

        $qNome = new Question('O nome completo da pessoa (nome do usuário): ');
        $nome = null;
        while(empty($nome)) {
            $nome = $helper->ask($input, $output, $qNome);
        }

        $qMail = new Question('O e-mail de contato (não será o usuário): ');
        $email = null;
        while(empty($email)) {
            $email = $helper->ask($input, $output, $qMail);
        }

        $qUsuario = new Question('O nome de usuário para login (o mesmo do AD/LDAP): ');
        $usuario = null;
        while(empty($usuario)) {
            $usuario = $helper->ask($input, $output, $qUsuario);
        }

        $question = new ConfirmationQuestion(
            'Este usuário deve ser ADMINISTRADOR (manipular outros usuários)? (yes/[no]) ',
            false
        );
        $roles = ['ROLE_USER'];
        if ($helper->ask($input, $output, $question)) {
            $roles[] = 'ROLE_ADMIN';
        }

        $objUsuario = new Usuario($usuario, $email, $nome);
        $objUsuario->setRoles($roles);
        $this->userRepository->add($objUsuario, true);

        $output->writeln(['','Usuário adicionado com sucesso','']);

        return Command::SUCCESS;
    }
}
