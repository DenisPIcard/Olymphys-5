<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230212201357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE odpf_memoires DROP FOREIGN KEY FK_7F26271E6D861B89');
        $this->addSql('ALTER TABLE odpf_videosequipes DROP FOREIGN KEY FK_8AC834BB6D861B89');
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE odpf_memoires');
        $this->addSql('DROP TABLE odpf_videosequipes');
        $this->addSql('ALTER TABLE odpf_fichierspasses CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE odpf_logos CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE odpf_partenaires DROP choix, DROP titre, DROP mecenes, DROP donateurs, DROP visites, DROP cadeaux, DROP cia, DROP updated_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, executed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE newsletter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, texte LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, envoyee TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT NULL, send_at DATETIME DEFAULT NULL, destinataires VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_memoires (id INT AUTO_INCREMENT NOT NULL, equipe_id INT DEFAULT NULL, type INT DEFAULT NULL, nomfichier VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME DEFAULT NULL, INDEX IDX_7F26271E6D861B89 (equipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_videosequipes (id INT AUTO_INCREMENT NOT NULL, equipe_id INT DEFAULT NULL, lien VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_8AC834BB6D861B89 (equipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE odpf_memoires ADD CONSTRAINT FK_7F26271E6D861B89 FOREIGN KEY (equipe_id) REFERENCES odpf_equipes_passees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE odpf_videosequipes ADD CONSTRAINT FK_8AC834BB6D861B89 FOREIGN KEY (equipe_id) REFERENCES odpf_equipes_passees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE odpf_fichierspasses CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE odpf_logos CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE odpf_partenaires ADD choix VARCHAR(255) DEFAULT NULL, ADD titre VARCHAR(255) DEFAULT NULL, ADD mecenes LONGTEXT DEFAULT NULL, ADD donateurs LONGTEXT DEFAULT NULL, ADD visites LONGTEXT DEFAULT NULL, ADD cadeaux LONGTEXT DEFAULT NULL, ADD cia LONGTEXT DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
    }
}
