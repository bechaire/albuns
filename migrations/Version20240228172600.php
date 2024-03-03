<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240228172600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE albuns CHANGE status status VARCHAR(1) NOT NULL COMMENT \'A = Ativo | I  = Inativo | C = Criado | X = Excluído\'');
        $this->addSql('ALTER TABLE fotos ADD arquivoorigem VARCHAR(150) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE albuns CHANGE status status VARCHAR(1) NOT NULL COMMENT \'A = Ativo | E = Em Espera | I  = Inativo (Remover futuramente)\'');
        $this->addSql('ALTER TABLE fotos DROP arquivoorigem');
    }
}
