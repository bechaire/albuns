<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240205014244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fotos ADD ordem INT NOT NULL, ADD identificador VARCHAR(15) NOT NULL, CHANGE arquivo arquivo VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE usuarios CHANGE usuario usuario VARCHAR(65) NOT NULL, CHANGE email email VARCHAR(120) NOT NULL, CHANGE roles roles JSON NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF687F22265B05D ON usuarios (usuario)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF687F2E7927C74 ON usuarios (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fotos DROP ordem, DROP identificador, CHANGE arquivo arquivo VARCHAR(45) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_EF687F22265B05D ON usuarios');
        $this->addSql('DROP INDEX UNIQ_EF687F2E7927C74 ON usuarios');
        $this->addSql('ALTER TABLE usuarios CHANGE usuario usuario VARCHAR(45) NOT NULL, CHANGE email email VARCHAR(75) NOT NULL, CHANGE roles roles VARCHAR(255) DEFAULT NULL');
    }
}
