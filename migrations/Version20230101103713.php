<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230101103713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipesadmin DROP FOREIGN KEY FK_A26C5EB174281A5E');
        $this->addSql('DROP INDEX IDX_A26C5EB174281A5E ON equipesadmin');
        $this->addSql('ALTER TABLE equipesadmin DROP edition_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipesadmin ADD edition_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipesadmin ADD CONSTRAINT FK_A26C5EB174281A5E FOREIGN KEY (edition_id) REFERENCES edition (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A26C5EB174281A5E ON equipesadmin (edition_id)');
    }
}
