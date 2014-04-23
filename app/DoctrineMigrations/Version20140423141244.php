<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140423141244 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE hn_facture_remboursement (rem_id INT AUTO_INCREMENT NOT NULL COMMENT 'ID du remboursement', ref_region INT DEFAULT NULL COMMENT 'ID de la référence', rem_intervention INT NOT NULL COMMENT 'Forfait intervention du remboursement', rem_supplement INT NOT NULL COMMENT 'Supplément formation du remboursement', rem_repas INT NOT NULL COMMENT 'Forfait repas du remboursement', rem_gestion INT NOT NULL COMMENT 'Frais de gestion administrative du remboursement', INDEX IDX_2E9AF1E77A7B998F (ref_region), PRIMARY KEY(rem_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE hn_facture_remboursement ADD CONSTRAINT FK_2E9AF1E77A7B998F FOREIGN KEY (ref_region) REFERENCES hn_reference (ref_id)");
        $this->addSql("ALTER TABLE hn_facture CHANGE fac_id fac_id INT AUTO_INCREMENT NOT NULL COMMENT 'ID de la facture', CHANGE fac_date_creation fac_date_creation DATETIME NOT NULL COMMENT 'Date de création de la facture'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE hn_facture_remboursement");
        $this->addSql("ALTER TABLE hn_facture CHANGE fac_id fac_id INT AUTO_INCREMENT NOT NULL, CHANGE fac_date_creation fac_date_creation DATETIME NOT NULL");
    }
}
