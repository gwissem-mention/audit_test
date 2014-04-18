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
    }
}
