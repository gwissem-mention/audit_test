ALTER TABLE hn_questionnaire_questionnaire ADD qst_occurrence_multiple TINYINT(1) DEFAULT '0' NOT NULL COMMENT 'Indique si le questionnaire peut être répondu plusieurs fois';
CREATE TABLE hn_questionnaire_occurrence (occ_id INT UNSIGNED AUTO_INCREMENT NOT NULL, occ_libelle VARCHAR(255) NOT NULL, PRIMARY KEY(occ_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_questionnaire_reponse ADD occ_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE hn_questionnaire_reponse ADD CONSTRAINT FK_BD9792282661557E FOREIGN KEY (occ_id) REFERENCES hn_questionnaire_occurrence (occ_id);
CREATE INDEX IDX_BD9792282661557E ON hn_questionnaire_reponse (occ_id);
