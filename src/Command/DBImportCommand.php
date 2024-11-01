<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Foto;
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
class DBImportCommand extends Command
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

        ini_set('memory_limit', '1048M');

        $output->writeln("LIMPANDO TABELAS...");
        $time = time();
        $this->clearTables();
        $timeTotal = time() - $time;
        $output->writeln("As tabelas foram limpas, tempo: {$timeTotal}s");
        
        $output->writeln("IMPORTANDO USUÁRIOS...");
        $time = time();
        $qtd = $this->importUsuarios($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados os {$qtd} usuários que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $output->writeln("IMPORTANDO INSTITUIÇÕES...");
        $time = time();
        $qtd = $this->importInstituicoes($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados as {$qtd} instituições que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $output->writeln("IMPORTANDO ÁLBUNS...");
        $time = time();
        $qtd = $this->importAlbuns($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados os {$qtd} álbuns que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $output->writeln("IMPORTANDO FOTOS...");
        $time = time();
        $qtd = $this->importPhotos($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados as {$qtd} fotos que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $output->writeln("IMPORTANDO LOGS...");
        $time = time();
        $qtd = $this->importVisitas($pdo);
        $timeTotal = time() - $time;
        $output->writeln("Foram importados os {$qtd} logs de visitas que existiam no banco de dados antigo, tempo: {$timeTotal}s");

        $this->updateAutoInc();

        return Command::SUCCESS;
    }

    /**
     * Atualizar autoinc para os últimos IDs + 1
     *
     * @return void
     */
    private function updateAutoInc(): void
    {
        $tablesKeys = [
            'albuns'       => 'idalbum',
            'fotos'        => 'idfoto',
            'instituicoes' => 'idinstituicao',
            'usuarios'     => 'idusuario',
            'visitas'      => 'idvisita',
        ];
        $sql = '';
        foreach ($tablesKeys as $tabela => $pk) {
            $sql .= <<<SQL
                SELECT @max := MAX({$pk}) + 1 FROM {$tabela};
                SET @stmt = concat('ALTER TABLE {$tabela} AUTO_INCREMENT = ', @max);
                PREPARE stmt FROM @stmt;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
            SQL;
        }

        $this->entityManager->getConnection()->executeQuery($sql);
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

    private function importUsuarios(\PDO $pdo): int
    {
        $sql = 'SELECT idusuario, email, nome, usuario_ad, ativo, remove
                FROM usuarios
                ORDER BY idusuario';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO usuarios (usuario, email, nome, roles, ativo, created, updated, idusuario) VALUES ';
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
        
        $insert = 'INSERT INTO instituicoes (sigla, nome, telefone, endereco, palavraschave, descricao, idinstituicao) VALUES ';
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
        
        $insert = 'INSERT INTO albuns (idalbum, idusuario, status, data, local, ano, titulo, addtag, acessos, instituicao, created, updated) VALUES ';
        foreach($rows as $row) {
            switch($row['status']) {
                case 'E':
                    $row['status'] = 'C';
                    break;
                case 'I':
                    $row['status'] = 'X';
                    break;
            }
            $insert .= sprintf('(%s, %s, "%s", "%s", "%s", "%s", "%s", "%s", %s, "%s", "%s", "%s"), ', 
                                $row['idalbum'], $row['idusuario'], $row['status'], $row['data'], $row['local'], $row['ano'], str_replace('"', '\"', $row['titulo']), $row['addtag'], $row['acessos'], $row['instituicao'], $row['datacad'], $row['datacad']);
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        return $result->rowCount();
    }

    private function importPhotos(\PDO $pdo): int
    {
        $sql = "SELECT f.idfoto, f.idalbum, f.arquivo, f.ordem, f.votos, f.visivel, f.arquivo_original, 
                       (CASE WHEN f.hash = a.hashdestaque THEN 'S' ELSE 'N' END) destaque
                FROM fotos f
                INNER JOIN albuns a ON a.idalbum = f.idalbum
                ORDER BY f.idalbum, f.idfoto";
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO fotos (idfoto, idalbum, arquivo, visivel, ordem, destaque, arquivoorigem, identificador, created, updated) VALUES ';
        $qtdRead = 0;
        $totalCount = 0;
        foreach($rows as $row) {
            $insert .= sprintf('(%s, %s, "%s", "%s", %s, "%s", "%s", "%s", now(), now()), ', 
                                $row['idfoto'], $row['idalbum'], $row['arquivo'], $row['visivel'], $row['ordem'], $row['destaque'], $row['arquivo_original'], Foto::criaNovoIdentificador());
            $qtdRead++;
            if ($qtdRead >= 500) {
                $insert = trim($insert, ', ');
                $result = $this->entityManager->getConnection()->executeQuery($insert);
                $totalCount += $result->rowCount();
                $qtdRead = 0;
                $insert = 'INSERT INTO fotos (idfoto, idalbum, arquivo, visivel, ordem, destaque, arquivoorigem, identificador, created, updated) VALUES ';
            }
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        $totalCount += $result->rowCount();

        // atualiza fotos destaque no álbum de fotos
        $update = <<<SQL
            UPDATE 
                albuns
            SET
                destaque = coalesce((SELECT identificador 
                                     FROM fotos 
                                     WHERE idalbum = albuns.idalbum 
                                     AND destaque = 'S' 
                                     AND visivel = 'S'), (SELECT identificador 
                                                          FROM fotos 
                                                          WHERE idalbum = albuns.idalbum 
                                                          AND destaque = 'N' 
                                                          AND visivel = 'S'
                                                          ORDER BY ordem, idfoto
                                                          LIMIT 1))
        SQL;
        $this->entityManager->getConnection()->executeQuery($update);

        return $totalCount;
    }

    private function importVisitas(\PDO $pdo): int
    {
        $sql = 'SELECT idalbum, datahora
                FROM visitas
                ORDER BY idvisitas';
        $rows = $pdo->query($sql);
        
        $insert = 'INSERT INTO visitas (idalbum, datahora) VALUES ';
        $qtdRead = 0;
        $totalCount = 0;
        foreach($rows as $row) {
            $insert .= sprintf('(%s, "%s"), ', $row['idalbum'], $row['datahora']);
            $qtdRead++;
            if ($qtdRead >= 1000) {
                $insert = trim($insert, ', ');
                $result = $this->entityManager->getConnection()->executeQuery($insert);
                $totalCount += $result->rowCount();
                $qtdRead = 0;
                $insert = 'INSERT INTO visitas (idalbum, datahora) VALUES ';
            }
        }
        $insert = trim($insert, ', ');

        $result = $this->entityManager->getConnection()->executeQuery($insert);
        $totalCount += $result->rowCount();
        return $totalCount;
    }
}
