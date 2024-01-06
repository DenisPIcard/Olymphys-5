<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231220201803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE centrescia CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE verou_classement verou_classement TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE coefficients CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE jures DROP A, DROP B, DROP C, DROP D, DROP E, DROP F, DROP G, DROP H, DROP I, DROP J, DROP K, DROP L, DROP M, DROP N, DROP O, DROP P, DROP Q, DROP R, DROP S, DROP T, DROP V, DROP W, DROP X, DROP Y, DROP Z');
        $this->addSql('ALTER TABLE notes CHANGE repquestions repquestions SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE odpf_equipes_passees DROP autorisations_photos');
        $this->addSql('DROP INDEX id ON uai');
        $this->addSql('ALTER TABLE uai CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY user_ibfk_2');
        $this->addSql('DROP INDEX IDX_8D93D64950D8F5D4 ON user');
        $this->addSql('ALTER TABLE user ADD centrecia VARCHAR(255) DEFAULT NULL, DROP centrecia_id');
        $this->addSql('ALTER TABLE visites ADD attribuee TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE centrescia CHANGE id id INT NOT NULL, CHANGE verou_classement verou_classement INT DEFAULT NULL');
        $this->addSql('ALTER TABLE coefficients CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE jures ADD A SMALLINT DEFAULT NULL, ADD B SMALLINT DEFAULT NULL, ADD C SMALLINT DEFAULT NULL, ADD D SMALLINT DEFAULT NULL, ADD E SMALLINT DEFAULT NULL, ADD F SMALLINT DEFAULT NULL, ADD G SMALLINT DEFAULT NULL, ADD H SMALLINT DEFAULT NULL, ADD I SMALLINT DEFAULT NULL, ADD J SMALLINT DEFAULT NULL, ADD K SMALLINT DEFAULT NULL, ADD L SMALLINT DEFAULT NULL, ADD M SMALLINT DEFAULT NULL, ADD N SMALLINT DEFAULT NULL, ADD O SMALLINT DEFAULT NULL, ADD P SMALLINT DEFAULT NULL, ADD Q SMALLINT DEFAULT NULL, ADD R SMALLINT DEFAULT NULL, ADD S SMALLINT DEFAULT NULL, ADD T SMALLINT DEFAULT NULL, ADD V SMALLINT DEFAULT NULL, ADD W SMALLINT DEFAULT NULL, ADD X SMALLINT DEFAULT NULL, ADD Y SMALLINT DEFAULT NULL, ADD Z SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE notes CHANGE repquestions repquestions INT DEFAULT NULL');
        $this->addSql('ALTER TABLE odpf_equipes_passees ADD autorisations_photos TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE uai CHANGE id id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX id ON uai (id)');
        $this->addSql('ALTER TABLE user ADD centrecia_id INT DEFAULT NULL, DROP centrecia');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT user_ibfk_2 FOREIGN KEY (centrecia_id) REFERENCES centrescia (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64950D8F5D4 ON user (centrecia_id)');
        $this->addSql('ALTER TABLE visites DROP attribuee');
    }
}
