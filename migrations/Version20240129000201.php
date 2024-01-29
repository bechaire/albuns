<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240129000201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE albuns (idalbum INT AUTO_INCREMENT NOT NULL, idusuario INT DEFAULT NULL, status VARCHAR(1) NOT NULL COMMENT \'A = Ativo | E = Em Espera | I  = Inativo (Remover futuramente)\', data DATE NOT NULL, local VARCHAR(255) DEFAULT NULL, ano VARCHAR(4) NOT NULL, addtag VARCHAR(1) NOT NULL, acessos INT DEFAULT NULL, instituicao VARCHAR(10) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_DFCF148CFD61E233 (idusuario), PRIMARY KEY(idalbum)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fotos (idfoto INT AUTO_INCREMENT NOT NULL, idalbum INT DEFAULT NULL, arquivo VARCHAR(45) DEFAULT NULL, youtubeid VARCHAR(15) DEFAULT NULL, visivel VARCHAR(1) NOT NULL, destaque VARCHAR(1) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_CB8405C7EF9D025A (idalbum), PRIMARY KEY(idfoto)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instituicoes (idinstituicao INT AUTO_INCREMENT NOT NULL, sigla VARCHAR(10) NOT NULL, nome VARCHAR(45) NOT NULL, telefone VARCHAR(15) NOT NULL, endereco VARCHAR(75) NOT NULL, palavraschave VARCHAR(150) NOT NULL, descricao VARCHAR(255) NOT NULL, PRIMARY KEY(idinstituicao)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios (idusuario INT AUTO_INCREMENT NOT NULL, roles VARCHAR(255) DEFAULT NULL, ativo VARCHAR(1) NOT NULL, ultacesso DATETIME DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, usuario VARCHAR(45) NOT NULL, email VARCHAR(75) NOT NULL, nome VARCHAR(65) NOT NULL, PRIMARY KEY(idusuario)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visitas (idvisita INT AUTO_INCREMENT NOT NULL, idalbum INT DEFAULT NULL, datahora DATETIME NOT NULL, userinfo VARCHAR(255) NOT NULL, INDEX IDX_2361FC87EF9D025A (idalbum), PRIMARY KEY(idvisita)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE albuns ADD CONSTRAINT FK_DFCF148CFD61E233 FOREIGN KEY (idusuario) REFERENCES usuarios (idusuario)');
        $this->addSql('ALTER TABLE fotos ADD CONSTRAINT FK_CB8405C7EF9D025A FOREIGN KEY (idalbum) REFERENCES albuns (idalbum)');
        $this->addSql('ALTER TABLE visitas ADD CONSTRAINT FK_2361FC87EF9D025A FOREIGN KEY (idalbum) REFERENCES albuns (idalbum)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE albuns DROP FOREIGN KEY FK_DFCF148CFD61E233');
        $this->addSql('ALTER TABLE fotos DROP FOREIGN KEY FK_CB8405C7EF9D025A');
        $this->addSql('ALTER TABLE visitas DROP FOREIGN KEY FK_2361FC87EF9D025A');
        $this->addSql('DROP TABLE albuns');
        $this->addSql('DROP TABLE fotos');
        $this->addSql('DROP TABLE instituicoes');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE visitas');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
