<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230210165028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE odpf_fichierspasses DROP FOREIGN KEY FK_DB9788F1FE20689C');
        $this->addSql('ALTER TABLE odpf_fichierspasses DROP FOREIGN KEY FK_DB9788F172389B72');
        $this->addSql('ALTER TABLE odpf_memoires DROP FOREIGN KEY FK_7F26271E6D861B89');
        $this->addSql('ALTER TABLE odpf_videosequipes DROP FOREIGN KEY FK_8AC834BB6D861B89');
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE odpf_documents');
        $this->addSql('DROP TABLE odpf_fichierspasses');
        $this->addSql('DROP TABLE odpf_logos');
        $this->addSql('DROP TABLE odpf_memoires');
        $this->addSql('DROP TABLE odpf_partenaires');
        $this->addSql('DROP TABLE odpf_videosequipes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, executed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE newsletter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, texte LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, envoyee TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT NULL, send_at DATETIME DEFAULT NULL, destinataires VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_documents (id INT AUTO_INCREMENT NOT NULL, fichier VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, titre VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_fichierspasses (id INT AUTO_INCREMENT NOT NULL, editionspassees_id INT DEFAULT NULL, equipepassee_id INT DEFAULT NULL, typefichier INT DEFAULT NULL, nomfichier VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME NOT NULL, national TINYINT(1) DEFAULT NULL, nomautorisation VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_DB9788F1A19E775 (editionspassees_id), INDEX IDX_DB9788F172389B72 (equipepassee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_logos (id INT AUTO_INCREMENT NOT NULL, lien VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, nom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, choix VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, part VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, alt VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, en_service TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_memoires (id INT AUTO_INCREMENT NOT NULL, equipe_id INT DEFAULT NULL, type INT DEFAULT NULL, nomfichier VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME DEFAULT NULL, INDEX IDX_7F26271E6D861B89 (equipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_partenaires (id INT AUTO_INCREMENT NOT NULL, choix VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, titre VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, mecenes LONGTEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, donateurs LONGTEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, visites LONGTEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, cadeaux LONGTEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, cia LONGTEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE odpf_videosequipes (id INT AUTO_INCREMENT NOT NULL, equipe_id INT DEFAULT NULL, lien VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_8AC834BB6D861B89 (equipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE odpf_fichierspasses ADD CONSTRAINT FK_DB9788F1FE20689C FOREIGN KEY (editionspassees_id) REFERENCES odpf_editions_passees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE odpf_fichierspasses ADD CONSTRAINT FK_DB9788F172389B72 FOREIGN KEY (equipepassee_id) REFERENCES odpf_equipes_passees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE odpf_memoires ADD CONSTRAINT FK_7F26271E6D861B89 FOREIGN KEY (equipe_id) REFERENCES odpf_equipes_passees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE odpf_videosequipes ADD CONSTRAINT FK_8AC834BB6D861B89 FOREIGN KEY (equipe_id) REFERENCES odpf_equipes_passees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
