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

        $this->clearTables();
        $this->addOldIDs();

        ini_set('memory_limit', '1048M');

        $time = time();
        $qtd = $this->importUsuarios($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados os {$qtd} usuários que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $time = time();
        $qtd = $this->importInstituicoes($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados as {$qtd} instituições que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $time = time();
        $qtd = $this->importAlbuns($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados os {$qtd} álbuns que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $time = time();
        $qtd = $this->importPhotos($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados as {$qtd} fotos que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $time = time();
        $qtd = $this->importVisitas($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados os {$qtd} logs de visitas que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $this->dropOldIDs();

        return Command::SUCCESS;
    }

    /**
     * Limpando dados antigos
     *
     * @return void
     */
    private function clearTables(): void
    {
        $sql = <<<SQL
            DELETE FROM instituicoes;
            DELETE FROM fotos;
            DELETE FROM visitas;
            DELETE FROM albuns;
            DELETE FROM usuarios;
        SQL;
        $this->entityManager->getConnection()->executeQuery($sql);
    }

    /**
     * adiciona colunas temporarias para lidar com os novos indices
     *
     * @param \PDO $pdo
     * @return void
     */
    private function addOldIDs(): void
    {
        $sql = <<<SQL
            ALTER TABLE instituicoes ADD COLUMN `idinstituicao_` INT;
            ALTER TABLE usuarios ADD COLUMN `idusuario_` INT;
            ALTER TABLE albuns ADD COLUMN `idalbum_` INT;
            ALTER TABLE albuns ADD COLUMN `hash_` VARCHAR(32);
            ALTER TABLE fotos ADD COLUMN `idfoto_` INT;
            ALTER TABLE fotos ADD COLUMN `hash_` VARCHAR(32);
            ALTER TABLE visitas ADD COLUMN `idalbum_` INT;
        SQL;
        $this->entityManager->getConnection()->executeQuery($sql);
    }

    /**
     * remove as colunas adicionadas
     *
     * @param \PDO $pdo
     * @return void
     */
    private function dropOldIDs(): void
    {
        $sql = <<<SQL
            ALTER TABLE instituicoes DROP COLUMN `idinstituicao_`;
            ALTER TABLE usuarios DROP COLUMN `idusuario_`;
            ALTER TABLE albuns DROP COLUMN `idalbum_`;
            ALTER TABLE albuns DROP COLUMN `hash_`;
            ALTER TABLE fotos DROP COLUMN `idfoto_`;
            ALTER TABLE fotos DROP COLUMN `hash_`;
            ALTER TABLE visitas DROP COLUMN `idalbum_`;
        SQL;
        $this->entityManager->getConnection()->executeQuery($sql);
    }

    private function importUsuarios(\PDO $pdo): int
    {
        $sql = 'SELECT idusuario, email, nome, usuario_ad, ativo, remove
                FROM usuarios
                ORDER BY idusuario';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO usuarios (usuario, email, nome, roles, ativo, created, updated, idusuario_) VALUES ';
        foreach($rows as $row) {
            $roles = ($row['remove']=='S') ? ['ROLE_USER', 'ROLE_ADMIN'] : ['ROLE_USER'];
            $insert .= sprintf('("%s", "%s", "%s", \'%s\', "%s", now(), now(), %s), ', $row['usuario_ad'], $row['email'], $row['nome'], json_encode($roles), $row['ativo'], $row['idusuario']);
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        return $result->rowCount();
    }

    private function importInstituicoes(\PDO $pdo): int
    {
        $sql = 'SELECT idinstituicao, sigla, nome, telefone, endereco, keywords, descricao
                FROM instituicoes
                ORDER BY idinstituicao';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO instituicoes (sigla, nome, telefone, endereco, palavraschave, descricao, idinstituicao_) VALUES ';
        foreach($rows as $row) {
            $insert .= sprintf('("%s", "%s", "%s", "%s", "%s", "%s", %s), ', $row['sigla'], $row['nome'], $row['telefone'], $row['endereco'], $row['keywords'], $row['descricao'], $row['idinstituicao']);
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        return $result->rowCount();
    }

    private function importAlbuns(\PDO $pdo): int
    {
        $sql = 'SELECT idalbum, idusuario, hashdestaque, instituicao, data, ano, titulo, local, datacad, addtag, status, acessos
                FROM albuns
                ORDER BY data, idalbum';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO albuns (idusuario, status, data, local, ano, titulo, addtag, acessos, instituicao, created, updated, idalbum_, hash_) VALUES ';
        foreach($rows as $row) {
            $insert .= sprintf('((select idusuario from usuarios where idusuario_ = %s), "%s", "%s", "%s", "%s", "%s", "%s", %s, "%s", "%s", "%s", %s, "%s"), ', 
                                $row['idusuario'], $row['status'], $row['data'], $row['local'], $row['ano'], str_replace('"', '\"', $row['titulo']), $row['addtag'], $row['acessos'], $row['instituicao'], $row['datacad'], $row['datacad'], $row['idalbum'], $row['hashdestaque']);
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        return $result->rowCount();
    }

    private function importPhotos(\PDO $pdo): int
    {
        $sql = 'SELECT idfoto, idalbum, arquivo, ordem, hash, votos, visivel, arquivo_original
                FROM fotos
                ORDER BY idalbum, idfoto';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO fotos (idalbum, arquivo, visivel, destaque, created, updated, ordem, identificador, idfoto_, hash_) VALUES ';
        $qtdRead = 0;
        $totalCount = 0;
        foreach($rows as $row) {
            $insert .= sprintf('((select idalbum from albuns where idalbum_ = %s), "%s", "%s", (select case when hash_ = "%s" then "S" else "N" end from albuns where idalbum_ = %s), now(), now(), %s, "%s", %s, "%s"), ', 
                                $row['idalbum'], $row['arquivo'], $row['visivel'], $row['hash'], $row['idalbum'], $row['ordem'], uniqid(), $row['idfoto'], $row['hash']);
            $qtdRead++;
            if ($qtdRead >= 500) {
                $insert = trim($insert, ', ');
                $result = $this->entityManager->getConnection()->executeQuery($insert);
                $totalCount += $result->rowCount();
                $qtdRead = 0;
                $insert = 'INSERT INTO fotos (idalbum, arquivo, visivel, destaque, created, updated, ordem, identificador, idfoto_, hash_) VALUES ';
            }
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        $totalCount += $result->rowCount();
        return $totalCount;
    }

    private function importVisitas(\PDO $pdo): int
    {
        $sql = 'SELECT idvisitas, idalbum, datahora
                FROM visitas
                ORDER BY idvisitas';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO visitas (idalbum, datahora, idalbum_) VALUES ';
        $qtdRead = 0;
        $totalCount = 0;
        foreach($rows as $row) {
            $insert .= sprintf('((select idalbum from albuns where idalbum_ = %s), "%s", %s), ', $row['idalbum'], $row['datahora'], $row['idalbum']);
            $qtdRead++;
            if ($qtdRead >= 1000) {
                $insert = trim($insert, ', ');
                $result = $this->entityManager->getConnection()->executeQuery($insert);
                $totalCount += $result->rowCount();
                $qtdRead = 0;
                $insert = 'INSERT INTO visitas (idalbum, datahora, idalbum_) VALUES ';
            }
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        $totalCount += $result->rowCount();
        return $totalCount;
    }
}
