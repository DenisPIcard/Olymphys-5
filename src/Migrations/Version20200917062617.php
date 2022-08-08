<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200917062617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adminsite CHANGE session session VARCHAR(255) DEFAULT NULL, CHANGE datelimite_cia datelimite_cia DATETIME DEFAULT NULL, CHANGE datelimite_nat datelimite_nat DATETIME DEFAULT NULL, CHANGE concours_cia concours_cia DATETIME DEFAULT NULL, CHANGE concours_cn concours_cn DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE cadeaux CHANGE contenu contenu VARCHAR(255) DEFAULT NULL, CHANGE fournisseur fournisseur VARCHAR(255) DEFAULT NULL, CHANGE montant montant NUMERIC(6, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE centrescia CHANGE id_orga1 id_orga1 INT DEFAULT NULL, CHANGE id_orga2 id_orga2 INT DEFAULT NULL, CHANGE id_jurycia id_jurycia INT DEFAULT NULL, CHANGE centre centre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE classement CHANGE niveau niveau VARCHAR(255) DEFAULT NULL, CHANGE montant montant NUMERIC(3, 0) DEFAULT NULL');
        $this->addSql('ALTER TABLE edition CHANGE ed ed VARCHAR(255) DEFAULT NULL, CHANGE date date DATETIME DEFAULT NULL, CHANGE edition edition INT DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE lieu lieu VARCHAR(255) DEFAULT NULL, CHANGE datelimite_cia datelimite_cia DATETIME DEFAULT NULL, CHANGE datelimite_nat datelimite_nat DATETIME DEFAULT NULL, CHANGE date_ouverture_site date_ouverture_site DATETIME DEFAULT NULL, CHANGE concours_cia concours_cia DATE DEFAULT NULL, CHANGE concours_cn concours_cn DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE eleves CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE classe classe VARCHAR(255) DEFAULT NULL, CHANGE lettre_equipe lettre_equipe VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE elevesinter CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE classe classe VARCHAR(255) DEFAULT NULL, CHANGE genre genre VARCHAR(1) DEFAULT NULL, CHANGE courriel courriel VARCHAR(60) DEFAULT NULL, CHANGE numsite numsite INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipes CHANGE visite_id visite_id INT DEFAULT NULL, CHANGE cadeau_id cadeau_id INT DEFAULT NULL, CHANGE phrases_id phrases_id INT DEFAULT NULL, CHANGE liaison_id liaison_id INT DEFAULT NULL, CHANGE prix_id prix_id INT DEFAULT NULL, CHANGE infoequipe_id infoequipe_id INT DEFAULT NULL, CHANGE ordre ordre SMALLINT DEFAULT NULL, CHANGE heure heure VARCHAR(255) DEFAULT NULL, CHANGE salle salle VARCHAR(255) DEFAULT NULL, CHANGE total total SMALLINT DEFAULT NULL, CHANGE classement classement VARCHAR(255) DEFAULT NULL, CHANGE rang rang SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipesadmin CHANGE centre_id centre_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE rne_id rne_id INT DEFAULT NULL, CHANGE lettre lettre VARCHAR(1) DEFAULT NULL, CHANGE numero numero SMALLINT DEFAULT NULL, CHANGE selectionnee selectionnee TINYINT(1) DEFAULT NULL, CHANGE titreProjet titreProjet VARCHAR(255) DEFAULT NULL, CHANGE nom_lycee nom_lycee VARCHAR(255) DEFAULT NULL, CHANGE denomination_lycee denomination_lycee VARCHAR(255) DEFAULT NULL, CHANGE lycee_localite lycee_localite VARCHAR(255) DEFAULT NULL, CHANGE lycee_academie lycee_academie VARCHAR(255) DEFAULT NULL, CHANGE id_prof1 id_prof1 INT DEFAULT NULL, CHANGE id_prof2 id_prof2 INT DEFAULT NULL, CHANGE prenom_prof1 prenom_prof1 VARCHAR(255) DEFAULT NULL, CHANGE nom_prof1 nom_prof1 VARCHAR(255) DEFAULT NULL, CHANGE prenom_prof2 prenom_prof2 VARCHAR(255) DEFAULT NULL, CHANGE nom_prof2 nom_prof2 VARCHAR(255) DEFAULT NULL, CHANGE rne rne VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fichiersequipes CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE fichier fichier VARCHAR(255) DEFAULT NULL, CHANGE typefichier typefichier INT DEFAULT NULL, CHANGE national national TINYINT(1) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE jures CHANGE A A SMALLINT DEFAULT NULL, CHANGE B B SMALLINT DEFAULT NULL, CHANGE C C SMALLINT DEFAULT NULL, CHANGE D D SMALLINT DEFAULT NULL, CHANGE E E SMALLINT DEFAULT NULL, CHANGE F F SMALLINT DEFAULT NULL, CHANGE G G SMALLINT DEFAULT NULL, CHANGE H H SMALLINT DEFAULT NULL, CHANGE I I SMALLINT DEFAULT NULL, CHANGE J J SMALLINT DEFAULT NULL, CHANGE K K SMALLINT DEFAULT NULL, CHANGE L L SMALLINT DEFAULT NULL, CHANGE M M SMALLINT DEFAULT NULL, CHANGE N N SMALLINT DEFAULT NULL, CHANGE O O SMALLINT DEFAULT NULL, CHANGE P P SMALLINT DEFAULT NULL, CHANGE Q Q SMALLINT DEFAULT NULL, CHANGE R R SMALLINT DEFAULT NULL, CHANGE S S SMALLINT DEFAULT NULL, CHANGE T T SMALLINT DEFAULT NULL, CHANGE U U SMALLINT DEFAULT NULL, CHANGE V V SMALLINT DEFAULT NULL, CHANGE W W SMALLINT DEFAULT NULL, CHANGE X X SMALLINT DEFAULT NULL, CHANGE Y Y SMALLINT DEFAULT NULL, CHANGE Z Z SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE liaison CHANGE liaison liaison VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE notes CHANGE ecrit ecrit SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE orgacia CHANGE centre_id centre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE palmares CHANGE a_id a_id INT DEFAULT NULL, CHANGE b_id b_id INT DEFAULT NULL, CHANGE c_id c_id INT DEFAULT NULL, CHANGE d_id d_id INT DEFAULT NULL, CHANGE e_id e_id INT DEFAULT NULL, CHANGE f_id f_id INT DEFAULT NULL, CHANGE g_id g_id INT DEFAULT NULL, CHANGE h_id h_id INT DEFAULT NULL, CHANGE i_id i_id INT DEFAULT NULL, CHANGE j_id j_id INT DEFAULT NULL, CHANGE k_id k_id INT DEFAULT NULL, CHANGE l_id l_id INT DEFAULT NULL, CHANGE m_id m_id INT DEFAULT NULL, CHANGE n_id n_id INT DEFAULT NULL, CHANGE o_id o_id INT DEFAULT NULL, CHANGE p_id p_id INT DEFAULT NULL, CHANGE q_id q_id INT DEFAULT NULL, CHANGE r_id r_id INT DEFAULT NULL, CHANGE s_id s_id INT DEFAULT NULL, CHANGE t_id t_id INT DEFAULT NULL, CHANGE u_id u_id INT DEFAULT NULL, CHANGE v_id v_id INT DEFAULT NULL, CHANGE w_id w_id INT DEFAULT NULL, CHANGE x_id x_id INT DEFAULT NULL, CHANGE y_id y_id INT DEFAULT NULL, CHANGE z_id z_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE photos CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE coment coment VARCHAR(125) DEFAULT NULL, CHANGE national national TINYINT(1) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE photoscn CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE thumb_id thumb_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE coment coment VARCHAR(125) DEFAULT NULL');
        $this->addSql('ALTER TABLE photoscnthumb CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE photosinter CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE thumb_id thumb_id INT DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE coment coment VARCHAR(125) DEFAULT NULL');
        $this->addSql('ALTER TABLE photosinterthumb CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE prix CHANGE prix prix VARCHAR(255) DEFAULT NULL, CHANGE classement classement VARCHAR(255) DEFAULT NULL, CHANGE voix voix VARCHAR(255) DEFAULT NULL, CHANGE intervenant intervenant VARCHAR(255) DEFAULT NULL, CHANGE remis_par remis_par VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rne CHANGE nature nature VARCHAR(255) DEFAULT NULL, CHANGE sigle sigle VARCHAR(11) DEFAULT NULL, CHANGE commune commune VARCHAR(255) DEFAULT NULL, CHANGE academie academie VARCHAR(255) DEFAULT NULL, CHANGE pays pays VARCHAR(255) DEFAULT NULL, CHANGE departement departement VARCHAR(255) DEFAULT NULL, CHANGE denomination_principale denomination_principale VARCHAR(255) DEFAULT NULL, CHANGE appellation_officielle appellation_officielle VARCHAR(255) DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE boite_postale boite_postale VARCHAR(255) DEFAULT NULL, CHANGE code_postal code_postal VARCHAR(255) DEFAULT NULL, CHANGE acheminement acheminement VARCHAR(255) DEFAULT NULL, CHANGE coordonnee_x coordonnee_x NUMERIC(10, 1) DEFAULT NULL, CHANGE coordonnee_y coordonnee_y NUMERIC(10, 1) DEFAULT NULL');
        $this->addSql('ALTER TABLE totalequipes CHANGE numero_equipe numero_equipe SMALLINT DEFAULT NULL, CHANGE lettre_equipe lettre_equipe VARCHAR(1) DEFAULT NULL, CHANGE nom_equipe nom_equipe VARCHAR(255) DEFAULT NULL, CHANGE nom_lycee nom_lycee VARCHAR(255) DEFAULT NULL, CHANGE denomination_lycee denomination_lycee VARCHAR(255) DEFAULT NULL, CHANGE lycee_localite lycee_localite VARCHAR(255) DEFAULT NULL, CHANGE lycee_academie lycee_academie VARCHAR(255) DEFAULT NULL, CHANGE id_prof1 id_prof1 SMALLINT DEFAULT NULL, CHANGE id_prof2 id_prof2 SMALLINT DEFAULT NULL, CHANGE prenom_prof1 prenom_prof1 VARCHAR(255) DEFAULT NULL, CHANGE nom_prof1 nom_prof1 VARCHAR(255) DEFAULT NULL, CHANGE prenom_prof2 prenom_prof2 VARCHAR(255) DEFAULT NULL, CHANGE nom_prof2 nom_prof2 VARCHAR(255) DEFAULT NULL, CHANGE rne rne VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE centre_id centre_id INT DEFAULT NULL, CHANGE is_active is_active TINYINT(1) DEFAULT NULL, CHANGE token token VARCHAR(255) DEFAULT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE rne rne VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE code code VARCHAR(11) DEFAULT NULL, CHANGE civilite civilite VARCHAR(15) DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(15) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL, CHANGE lastVisit lastVisit DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE videosequipes CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE visites CHANGE intitule intitule VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adminsite CHANGE session session VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE datelimite_cia datelimite_cia DATETIME DEFAULT \'NULL\', CHANGE datelimite_nat datelimite_nat DATETIME DEFAULT \'NULL\', CHANGE concours_cia concours_cia DATETIME DEFAULT \'NULL\', CHANGE concours_cn concours_cn DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE cadeaux CHANGE contenu contenu VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE fournisseur fournisseur VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE montant montant NUMERIC(6, 2) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE centrescia CHANGE id_orga1 id_orga1 INT DEFAULT NULL, CHANGE id_orga2 id_orga2 INT DEFAULT NULL, CHANGE id_jurycia id_jurycia INT DEFAULT NULL, CHANGE centre centre VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE classement CHANGE niveau niveau VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE montant montant NUMERIC(3, 0) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE edition CHANGE ed ed VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE date date DATETIME DEFAULT \'NULL\', CHANGE edition edition INT DEFAULT NULL, CHANGE ville ville VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lieu lieu VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE datelimite_cia datelimite_cia DATETIME DEFAULT \'NULL\', CHANGE datelimite_nat datelimite_nat DATETIME DEFAULT \'NULL\', CHANGE date_ouverture_site date_ouverture_site DATETIME DEFAULT \'NULL\', CHANGE concours_cia concours_cia DATE DEFAULT \'NULL\', CHANGE concours_cn concours_cn DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE eleves CHANGE nom nom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE prenom prenom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE classe classe VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lettre_equipe lettre_equipe VARCHAR(1) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE elevesinter CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE numsite numsite INT DEFAULT NULL, CHANGE nom nom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE prenom prenom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE genre genre VARCHAR(1) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE classe classe VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE courriel courriel VARCHAR(60) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE equipes CHANGE visite_id visite_id INT DEFAULT NULL, CHANGE cadeau_id cadeau_id INT DEFAULT NULL, CHANGE phrases_id phrases_id INT DEFAULT NULL, CHANGE liaison_id liaison_id INT DEFAULT NULL, CHANGE prix_id prix_id INT DEFAULT NULL, CHANGE infoequipe_id infoequipe_id INT DEFAULT NULL, CHANGE ordre ordre SMALLINT DEFAULT NULL, CHANGE heure heure VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE salle salle VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE total total SMALLINT DEFAULT NULL, CHANGE classement classement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE rang rang SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipesadmin CHANGE centre_id centre_id INT DEFAULT NULL, CHANGE rne_id rne_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE lettre lettre VARCHAR(1) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE numero numero SMALLINT DEFAULT NULL, CHANGE selectionnee selectionnee TINYINT(1) DEFAULT \'NULL\', CHANGE titreProjet titreProjet VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom_lycee nom_lycee VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE denomination_lycee denomination_lycee VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lycee_localite lycee_localite VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lycee_academie lycee_academie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE prenom_prof1 prenom_prof1 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom_prof1 nom_prof1 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE prenom_prof2 prenom_prof2 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom_prof2 nom_prof2 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE rne rne VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE id_prof1 id_prof1 INT DEFAULT NULL, CHANGE id_prof2 id_prof2 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fichiersequipes CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE fichier fichier VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE typefichier typefichier INT DEFAULT NULL, CHANGE national national TINYINT(1) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE jures CHANGE A A SMALLINT DEFAULT NULL, CHANGE B B SMALLINT DEFAULT NULL, CHANGE C C SMALLINT DEFAULT NULL, CHANGE D D SMALLINT DEFAULT NULL, CHANGE E E SMALLINT DEFAULT NULL, CHANGE F F SMALLINT DEFAULT NULL, CHANGE G G SMALLINT DEFAULT NULL, CHANGE H H SMALLINT DEFAULT NULL, CHANGE I I SMALLINT DEFAULT NULL, CHANGE J J SMALLINT DEFAULT NULL, CHANGE K K SMALLINT DEFAULT NULL, CHANGE L L SMALLINT DEFAULT NULL, CHANGE M M SMALLINT DEFAULT NULL, CHANGE N N SMALLINT DEFAULT NULL, CHANGE O O SMALLINT DEFAULT NULL, CHANGE P P SMALLINT DEFAULT NULL, CHANGE Q Q SMALLINT DEFAULT NULL, CHANGE R R SMALLINT DEFAULT NULL, CHANGE S S SMALLINT DEFAULT NULL, CHANGE T T SMALLINT DEFAULT NULL, CHANGE U U SMALLINT DEFAULT NULL, CHANGE V V SMALLINT DEFAULT NULL, CHANGE W W SMALLINT DEFAULT NULL, CHANGE X X SMALLINT DEFAULT NULL, CHANGE Y Y SMALLINT DEFAULT NULL, CHANGE Z Z SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE liaison CHANGE liaison liaison VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE notes CHANGE ecrit ecrit SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE orgacia CHANGE centre_id centre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE palmares CHANGE a_id a_id INT DEFAULT NULL, CHANGE b_id b_id INT DEFAULT NULL, CHANGE c_id c_id INT DEFAULT NULL, CHANGE d_id d_id INT DEFAULT NULL, CHANGE e_id e_id INT DEFAULT NULL, CHANGE f_id f_id INT DEFAULT NULL, CHANGE g_id g_id INT DEFAULT NULL, CHANGE h_id h_id INT DEFAULT NULL, CHANGE i_id i_id INT DEFAULT NULL, CHANGE j_id j_id INT DEFAULT NULL, CHANGE k_id k_id INT DEFAULT NULL, CHANGE l_id l_id INT DEFAULT NULL, CHANGE m_id m_id INT DEFAULT NULL, CHANGE n_id n_id INT DEFAULT NULL, CHANGE o_id o_id INT DEFAULT NULL, CHANGE p_id p_id INT DEFAULT NULL, CHANGE q_id q_id INT DEFAULT NULL, CHANGE r_id r_id INT DEFAULT NULL, CHANGE s_id s_id INT DEFAULT NULL, CHANGE t_id t_id INT DEFAULT NULL, CHANGE u_id u_id INT DEFAULT NULL, CHANGE v_id v_id INT DEFAULT NULL, CHANGE w_id w_id INT DEFAULT NULL, CHANGE x_id x_id INT DEFAULT NULL, CHANGE y_id y_id INT DEFAULT NULL, CHANGE z_id z_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE photos CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE photo photo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE coment coment VARCHAR(125) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE national national TINYINT(1) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE photoscn CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE thumb_id thumb_id INT DEFAULT NULL, CHANGE photo photo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE coment coment VARCHAR(125) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE photoscnthumb CHANGE photo photo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE photosinter CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE thumb_id thumb_id INT DEFAULT NULL, CHANGE photo photo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE coment coment VARCHAR(125) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE photosinterthumb CHANGE photo photo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE prix CHANGE prix prix VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE classement classement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE voix voix VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE intervenant intervenant VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE remis_par remis_par VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rne CHANGE commune commune VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE academie academie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE pays pays VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE departement departement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE appellation_officielle appellation_officielle VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE adresse adresse VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE boite_postale boite_postale VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE code_postal code_postal VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE sigle sigle VARCHAR(11) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE denomination_principale denomination_principale VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE acheminement acheminement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE coordonnee_x coordonnee_x NUMERIC(10, 1) DEFAULT \'NULL\', CHANGE coordonnee_y coordonnee_y NUMERIC(10, 1) DEFAULT \'NULL\', CHANGE nature nature VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom nom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE totalequipes CHANGE numero_equipe numero_equipe SMALLINT DEFAULT NULL, CHANGE lettre_equipe lettre_equipe VARCHAR(1) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom_equipe nom_equipe VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom_lycee nom_lycee VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE denomination_lycee denomination_lycee VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lycee_localite lycee_localite VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lycee_academie lycee_academie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE id_prof1 id_prof1 SMALLINT DEFAULT NULL, CHANGE id_prof2 id_prof2 SMALLINT DEFAULT NULL, CHANGE prenom_prof1 prenom_prof1 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom_prof1 nom_prof1 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE prenom_prof2 prenom_prof2 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom_prof2 nom_prof2 VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE rne rne VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE centre_id centre_id INT DEFAULT NULL, CHANGE is_active is_active TINYINT(1) DEFAULT \'NULL\', CHANGE token token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\', CHANGE rne rne VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nom nom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE prenom prenom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE adresse adresse VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE ville ville VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE code code VARCHAR(11) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE phone phone VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\', CHANGE lastVisit lastVisit DATETIME DEFAULT \'NULL\', CHANGE civilite civilite VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE videosequipes CHANGE edition_id edition_id INT DEFAULT NULL, CHANGE equipe_id equipe_id INT DEFAULT NULL, CHANGE nom nom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE visites CHANGE intitule intitule VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}