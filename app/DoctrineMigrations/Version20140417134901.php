<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140417134901 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE hn_module_session (ses_id INT AUTO_INCREMENT NOT NULL, mod_module INT DEFAULT NULL, ref_duree INT DEFAULT NULL COMMENT 'ID de la référence', ref_etat INT DEFAULT NULL COMMENT 'ID de la référence', ses_date_session DATETIME NOT NULL COMMENT 'Date de la Session', ses_dateOuvertureInscription DATETIME NOT NULL, ses_dateFermetureInscription DATETIME NOT NULL, ses_horaires LONGTEXT NOT NULL COMMENT 'Horaires de la session', ses_lieu LONGTEXT NOT NULL COMMENT 'Lieu de la session', ses_description LONGTEXT NOT NULL COMMENT 'Description de la session', ses_nombrePlaceDisponible INT DEFAULT NULL COMMENT 'Nombre de places disponibles de la session', ses_path VARCHAR(255) DEFAULT NULL, INDEX IDX_354C27A478931957 (mod_module), INDEX IDX_354C27A4F79DA5F7 (ref_duree), INDEX IDX_354C27A442B9B6E5 (ref_etat), PRIMARY KEY(ses_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE hn_module_session_rolerestriction (ses_id INT NOT NULL, ro_id INT NOT NULL COMMENT 'ID du groupe', INDEX IDX_1610351C8D2C35A1 (ses_id), INDEX IDX_1610351CBF75BFC5 (ro_id), PRIMARY KEY(ses_id, ro_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE hn_module (mod_id INT AUTO_INCREMENT NOT NULL, ref_duree INT DEFAULT NULL COMMENT 'ID de la référence', usr_formateur INT DEFAULT NULL COMMENT 'ID de l utilisateur', ref_statut INT DEFAULT NULL COMMENT 'ID de la référence', mod_titre VARCHAR(255) NOT NULL COMMENT 'Titre du module', mod_horairesType VARCHAR(255) DEFAULT NULL COMMENT 'Horaires type du module', mod_lieu LONGTEXT DEFAULT NULL COMMENT 'Lieu du module', mod_description LONGTEXT DEFAULT NULL COMMENT 'Description du module', mod_nombrePlaceDisponible INT DEFAULT NULL COMMENT 'Nombre de places disponibles du module', mod_prerequis LONGTEXT DEFAULT NULL COMMENT 'Prérequis du module', mod_piecejointe VARCHAR(255) DEFAULT NULL COMMENT 'Nom du fichier stocké', INDEX IDX_ACA89B4EF79DA5F7 (ref_duree), INDEX IDX_ACA89B4E1E558607 (usr_formateur), INDEX IDX_ACA89B4E907D9846 (ref_statut), PRIMARY KEY(mod_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE hn_module_objet (mod_id INT NOT NULL, obj_id INT NOT NULL COMMENT 'ID de l objet', INDEX IDX_7CC4857338E21CD (mod_id), INDEX IDX_7CC485766093344 (obj_id), PRIMARY KEY(mod_id, obj_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE hn_module_session ADD CONSTRAINT FK_354C27A478931957 FOREIGN KEY (mod_module) REFERENCES hn_module (mod_id)");
        $this->addSql("ALTER TABLE hn_module_session ADD CONSTRAINT FK_354C27A4F79DA5F7 FOREIGN KEY (ref_duree) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_module_session ADD CONSTRAINT FK_354C27A442B9B6E5 FOREIGN KEY (ref_etat) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_module_session_rolerestriction ADD CONSTRAINT FK_1610351C8D2C35A1 FOREIGN KEY (ses_id) REFERENCES hn_module_session (ses_id)");
        $this->addSql("ALTER TABLE hn_module_session_rolerestriction ADD CONSTRAINT FK_1610351CBF75BFC5 FOREIGN KEY (ro_id) REFERENCES core_role (ro_id)");
        $this->addSql("ALTER TABLE hn_module ADD CONSTRAINT FK_ACA89B4EF79DA5F7 FOREIGN KEY (ref_duree) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_module ADD CONSTRAINT FK_ACA89B4E1E558607 FOREIGN KEY (usr_formateur) REFERENCES core_user (usr_id)");
        $this->addSql("ALTER TABLE hn_module ADD CONSTRAINT FK_ACA89B4E907D9846 FOREIGN KEY (ref_statut) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_module_objet ADD CONSTRAINT FK_7CC4857338E21CD FOREIGN KEY (mod_id) REFERENCES hn_module (mod_id)");
        $this->addSql("ALTER TABLE hn_module_objet ADD CONSTRAINT FK_7CC485766093344 FOREIGN KEY (obj_id) REFERENCES hn_objet (obj_id)");
        $this->addSql("INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (125, NULL, 1, 'Gestion des modules', 'hopitalnumerique_module_module', '[]', 0, NULL, 'fa fa-indent', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7), (126, 125, 1, 'Ajouter un module', 'hopitalnumerique_module_module_add', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3), (127, 125, 1, 'Fiche d''un module', 'hopitalnumerique_module_module_show', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2), (128, 125, 1, 'Editer un module', 'hopitalnumerique_module_module_edit', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1), (129, 125, 1, 'Liste des sessions d''un module', 'hopitalnumerique_module_module_session', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4), (130, 125, 1, 'Ajouter une session à un module', 'hopitalnumerique_module_module_session_add', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 5), (131, 125, 1, 'Editer une session d''un module', 'hopitalnumerique_module_module_session_edit', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 6), (132, 125, 1, 'Afficher une session d''un module', 'hopitalnumerique_module_module_session_show', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE hn_module_session_rolerestriction DROP FOREIGN KEY FK_1610351C8D2C35A1");
        $this->addSql("ALTER TABLE hn_module_session DROP FOREIGN KEY FK_354C27A478931957");
        $this->addSql("ALTER TABLE hn_module_objet DROP FOREIGN KEY FK_7CC4857338E21CD");
        $this->addSql("DROP TABLE hn_module_session");
        $this->addSql("DROP TABLE hn_module_session_rolerestriction");
        $this->addSql("DROP TABLE hn_module");
        $this->addSql("DROP TABLE hn_module_objet");
    }
}
