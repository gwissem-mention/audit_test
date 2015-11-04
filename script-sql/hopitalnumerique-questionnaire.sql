ALTER TABLE hn_questionnaire_questionnaire ADD qst_occurrence_multiple TINYINT(1) DEFAULT '0' NOT NULL COMMENT 'Indique si le questionnaire peut être répondu plusieurs fois';
CREATE TABLE hn_questionnaire_occurrence (occ_id INT UNSIGNED AUTO_INCREMENT NOT NULL, occ_libelle VARCHAR(255) NOT NULL, PRIMARY KEY(occ_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_questionnaire_occurrence CHANGE occ_libelle occ_libelle VARCHAR(255) DEFAULT NULL;

ALTER TABLE hn_questionnaire_occurrence ADD qst_id INT NOT NULL COMMENT 'ID du questionnaire', ADD usr_id INT NOT NULL COMMENT 'ID de l utilisateur';
ALTER TABLE hn_questionnaire_occurrence ADD CONSTRAINT FK_CE606DBB293CE31 FOREIGN KEY (qst_id) REFERENCES hn_questionnaire_questionnaire (qst_id) ON DELETE CASCADE;
ALTER TABLE hn_questionnaire_occurrence ADD CONSTRAINT FK_CE606DBC69D3FB FOREIGN KEY (usr_id) REFERENCES core_user (usr_id) ON DELETE CASCADE;
CREATE INDEX IDX_CE606DBB293CE31 ON hn_questionnaire_occurrence (qst_id);
CREATE INDEX IDX_CE606DBC69D3FB ON hn_questionnaire_occurrence (usr_id);

ALTER TABLE hn_questionnaire_reponse ADD occ_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE hn_questionnaire_reponse ADD CONSTRAINT FK_BD9792282661557E FOREIGN KEY (occ_id) REFERENCES hn_questionnaire_occurrence (occ_id);
CREATE INDEX IDX_BD9792282661557E ON hn_questionnaire_reponse (occ_id);

CREATE UNIQUE INDEX QUESTION_OCCURRENCE_USER ON hn_questionnaire_reponse (que_id, usr_id, occ_id);
