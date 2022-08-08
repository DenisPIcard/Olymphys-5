<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200502134018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        // $this->addSql('DROP INDEX IDX_8D93D649463CD7C3 ON user');

        $this->addSql('ALTER TABLE rne CHANGE rne rne VARCHAR(10) NOT NULL, CHANGE nature nature VARCHAR(255) DEFAULT NULL, CHANGE sigle sigle VARCHAR(50) DEFAULT NULL, CHANGE commune commune VARCHAR(255) DEFAULT NULL, CHANGE academie academie VARCHAR(255) DEFAULT NULL, CHANGE pays pays VARCHAR(255) DEFAULT NULL, CHANGE departement departement VARCHAR(255) DEFAULT NULL, CHANGE denomination_principale denomination_principale VARCHAR(255) DEFAULT NULL, CHANGE appellation_officielle appellation_officielle VARCHAR(255) DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE boite_postale boite_postale VARCHAR(255) DEFAULT NULL, CHANGE code_postal code_postal VARCHAR(255) DEFAULT NULL, CHANGE acheminement acheminement VARCHAR(255) DEFAULT NULL, CHANGE coordonnee_x coordonnee_x NUMERIC(10, 1) DEFAULT NULL, CHANGE coordonnee_y coordonnee_y NUMERIC(10, 1) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6E92B6D26E92B6D2 ON rne (rne)');
        $this->addSql('ALTER TABLE edition ADD concours_cia DATETIME DEFAULT NULL, ADD concours_cn DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('DROP INDEX IDX_A26C5EB174281A5E ON equipesadmin');
        $this->addSql('ALTER TABLE equipesadmin ADD CONSTRAINT equipesadmin_ibfk_1 FOREIGN KEY (id_prof1) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipesadmin ADD CONSTRAINT equipesadmin_ibfk_2 FOREIGN KEY (id_prof2) REFERENCES user (id)');
        $this->addSql('CREATE INDEX idProf1 ON equipesadmin (id_prof1)');
        $this->addSql('CREATE INDEX idProf2 ON equipesadmin (id_prof2)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A26C5EB1F55AE19E ON equipesadmin (numero)');
        $this->addSql('ALTER TABLE equipesadmin RENAME INDEX idx_a26c5eb144d2df56 TO rne_id');
        $this->addSql('DROP INDEX UNIQ_6E92B6D26E92B6D2 ON rne');
        $this->addSql('ALTER TABLE rne CHANGE rne rne TEXT CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, CHANGE commune commune VARCHAR(150) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE academie academie TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE pays pays TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE departement departement TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE appellation_officielle appellation_officielle TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE adresse adresse TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE boite_postale boite_postale TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE code_postal code_postal TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE sigle sigle TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE denomination_principale denomination_principale VARCHAR(100) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE acheminement acheminement VARCHAR(50) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE coordonnee_x coordonnee_x NUMERIC(9, 1) DEFAULT NULL, CHANGE coordonnee_y coordonnee_y NUMERIC(9, 1) DEFAULT NULL, CHANGE nature nature TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE nom nom VARCHAR(50) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('ALTER TABLE user ADD centre_id INT DEFAULT NULL, CHANGE agreed_terms_at agreed_terms_at DATETIME DEFAULT NULL, CHANGE email email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE INDEX IDX_8D93D649463CD7C3 ON user (centre_id)');
    }
}
