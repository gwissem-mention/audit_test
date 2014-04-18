<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140418112715 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE hn_module_session_inscription (insc_id INT AUTO_INCREMENT NOT NULL, ses_session INT DEFAULT NULL, usr_formateur INT DEFAULT NULL COMMENT 'ID de l utilisateur', ref_etat_inscription INT DEFAULT NULL COMMENT 'ID de la référence', ref_etat_participation INT DEFAULT NULL COMMENT 'ID de la référence', ref_etat_evaluation INT DEFAULT NULL COMMENT 'ID de la référence', insc_commentaire LONGTEXT NOT NULL, INDEX IDX_345A33A833895EFA (ses_session), INDEX IDX_345A33A81E558607 (usr_formateur), INDEX IDX_345A33A8E5A20F62 (ref_etat_inscription), INDEX IDX_345A33A8EF9D7879 (ref_etat_participation), INDEX IDX_345A33A82A9D12C0 (ref_etat_evaluation), PRIMARY KEY(insc_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE hn_module_session_inscription ADD CONSTRAINT FK_345A33A833895EFA FOREIGN KEY (ses_session) REFERENCES hn_module_session (ses_id)");
        $this->addSql("ALTER TABLE hn_module_session_inscription ADD CONSTRAINT FK_345A33A81E558607 FOREIGN KEY (usr_formateur) REFERENCES core_user (usr_id)");
        $this->addSql("ALTER TABLE hn_module_session_inscription ADD CONSTRAINT FK_345A33A8E5A20F62 FOREIGN KEY (ref_etat_inscription) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_module_session_inscription ADD CONSTRAINT FK_345A33A8EF9D7879 FOREIGN KEY (ref_etat_participation) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_module_session_inscription ADD CONSTRAINT FK_345A33A82A9D12C0 FOREIGN KEY (ref_etat_evaluation) REFERENCES hn_reference (ref_id)");
        $this->addSql("DROP TABLE core_user_role");
        $this->addSql("DROP TABLE hn_module_module");
        $this->addSql("ALTER TABLE hn_module_session CHANGE ses_horaires ses_horaires LONGTEXT NOT NULL COMMENT 'Horaires de la session', CHANGE ses_lieu ses_lieu LONGTEXT NOT NULL COMMENT 'Lieu de la session', CHANGE ses_description ses_description LONGTEXT NOT NULL COMMENT 'Description de la session'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE core_user_role (usr_id INT NOT NULL COMMENT 'ID de l utilisateur', ro_id INT NOT NULL COMMENT 'ID du groupe', INDEX IDX_6288F687C69D3FB (usr_id), INDEX IDX_6288F687BF75BFC5 (ro_id), PRIMARY KEY(usr_id, ro_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE hn_module_module (mod_id INT AUTO_INCREMENT NOT NULL, ref_statut INT DEFAULT NULL COMMENT 'ID de la référence', usr_formateur INT DEFAULT NULL COMMENT 'ID de l utilisateur', ref_duree INT DEFAULT NULL COMMENT 'ID de la référence', mod_titre VARCHAR(255) NOT NULL COMMENT 'Titre du module', mod_horairesType VARCHAR(255) DEFAULT NULL COMMENT 'Horaires type du module', mod_lieu LONGTEXT DEFAULT NULL COMMENT 'Lieu du module', mod_description LONGTEXT DEFAULT NULL COMMENT 'Description du module', mod_nombrePlaceDisponible INT DEFAULT NULL COMMENT 'Nombre de places disponibles du module', mod_prerequis LONGTEXT DEFAULT NULL COMMENT 'Prérequis du module', mod_piecejointe VARCHAR(255) DEFAULT NULL COMMENT 'Nom du fichier stocké', INDEX IDX_D1685BE5F79DA5F7 (ref_duree), INDEX IDX_D1685BE51E558607 (usr_formateur), INDEX IDX_D1685BE5907D9846 (ref_statut), PRIMARY KEY(mod_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE core_user_role ADD CONSTRAINT FK_6288F687BF75BFC5 FOREIGN KEY (ro_id) REFERENCES core_role (ro_id)");
        $this->addSql("ALTER TABLE core_user_role ADD CONSTRAINT FK_6288F687C69D3FB FOREIGN KEY (usr_id) REFERENCES core_user (usr_id)");
        $this->addSql("ALTER TABLE hn_module_module ADD CONSTRAINT FK_D1685BE5907D9846 FOREIGN KEY (ref_statut) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_module_module ADD CONSTRAINT FK_D1685BE51E558607 FOREIGN KEY (usr_formateur) REFERENCES core_user (usr_id)");
        $this->addSql("ALTER TABLE hn_module_module ADD CONSTRAINT FK_D1685BE5F79DA5F7 FOREIGN KEY (ref_duree) REFERENCES hn_reference (ref_id)");
        $this->addSql("DROP TABLE hn_module_session_inscription");
        $this->addSql("ALTER TABLE hn_module_session CHANGE ses_horaires ses_horaires LONGTEXT DEFAULT NULL COMMENT 'Horaires de la session', CHANGE ses_lieu ses_lieu LONGTEXT DEFAULT NULL COMMENT 'Lieu de la session', CHANGE ses_description ses_description LONGTEXT DEFAULT NULL COMMENT 'Description de la session'");
    }
}
