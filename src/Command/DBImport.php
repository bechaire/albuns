<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:db-import',
    description: 'Limpa banco local e importa dados',
    hidden: false,
    aliases: ['app:import-db']
)]
class DBImport extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $output->writeln([
            'Importando dados do sistema atual',
            '==================================================================',
            '',
        ]);

        $pdo = new \PDO('mysql:host=192.168.20.6;dbname=albuns', 'uAlbuns', 'nd65dhok79e', array(\PDO::MYSQL_ATTR_INIT_COMMAND=>'set names utf8'));
        
        $qtdUsersAdd = $this->createUsers($pdo);
        $output->writeln("Foram importados os {$qtdUsersAdd} usuários que existiam no banco de dados antigo");

        

        return Command::SUCCESS;
    }

    private function createUsers(\PDO $pdo): int
    {
        $sql = 'SELECT idusuario, email, nome, usuario_ad, ativo, remove
                FROM usuarios
                ORDER BY idusuario';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO usuarios (usuario, email, nome, roles, ativo, created, updated) VALUES ';
        foreach($rows as $row) {
            $insert .= sprintf('("%s", "%s", "%s", "[\"ROLE_USER\"]", "%s", now(), now()), ', $row['usuario_ad'], $row['email'], $row['nome'], $row['ativo']);
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        return $result->rowCount();
    }
}
