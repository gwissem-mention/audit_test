<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140418141206 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE hn_module_session_inscription DROP FOREIGN KEY FK_345A33A81E558607");
        $this->addSql("DROP INDEX IDX_345A33A81E558607 ON hn_module_session_inscription");
        $this->addSql("ALTER TABLE hn_module_session_inscription CHANGE usr_formateur usr_participant INT DEFAULT NULL COMMENT 'ID de l utilisateur'");
        $this->addSql("ALTER TABLE hn_module_session_inscription ADD CONSTRAINT FK_345A33A85C592711 FOREIGN KEY (usr_participant) REFERENCES core_user (usr_id)");
        $this->addSql("CREATE INDEX IDX_345A33A85C592711 ON hn_module_session_inscription (usr_participant)");
        //Mails des inscriptions
        $this->addSql("INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`) VALUES (31, '[HOPITALNUMERIQUE] - Inscription à une session', 'Acceptation de l\'inscription à une session d\'un module', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\r\nVôtre inscription à la session %module du %date a été accepté.\r\n\r\nCordialement,', '{"%u":"Nom d''utilisateur","%module":"Nom du module","%date":"Date de la session"}')");
        $this->addSql("INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`) VALUES (32, '[HOPITALNUMERIQUE] - Inscription à une session', 'Refus de l\'inscription à une session d\'un module', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\r\nVôtre inscription à la session %module du %date a été refusé.\r\n\r\nCordialement,', '{"%u":"Nom d''utilisateur","%module":"Nom du module","%date":"Date de la session"}');");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE hn_module_session_inscription DROP FOREIGN KEY FK_345A33A85C592711");
        $this->addSql("DROP INDEX IDX_345A33A85C592711 ON hn_module_session_inscription");
        $this->addSql("ALTER TABLE hn_module_session_inscription CHANGE usr_participant usr_formateur INT DEFAULT NULL COMMENT 'ID de l utilisateur'");
        $this->addSql("ALTER TABLE hn_module_session_inscription ADD CONSTRAINT FK_345A33A81E558607 FOREIGN KEY (usr_formateur) REFERENCES core_user (usr_id)");
        $this->addSql("CREATE INDEX IDX_345A33A81E558607 ON hn_module_session_inscription (usr_formateur)");
        //Mails des inscriptions
        $this->addSql("DELETE FROM `core_mail` WHERE `core_mail`.`mail_id` = 31");
        $this->addSql("DELETE FROM `core_mail` WHERE `core_mail`.`mail_id` = 32");
    }
}
