CREATE TABLE hn_fichier_type (fictyp_id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, fictypc_libelle VARCHAR(32) NOT NULL, PRIMARY KEY(fictyp_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE hn_fichier_extension (ext_id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, fictyp_id SMALLINT UNSIGNED NOT NULL, ext_valeur VARCHAR(8) NOT NULL, INDEX IDX_52FEB0C22AC470BF (fictyp_id), PRIMARY KEY(ext_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_fichier_extension ADD CONSTRAINT FK_52FEB0C22AC470BF FOREIGN KEY (fictyp_id) REFERENCES hn_fichier_type (fictyp_id) ON DELETE CASCADE;

INSERT INTO `hn_fichier_type` (`fictyp_id`, `fictypc_libelle`) VALUES ('1', 'Tableur'), ('2', 'Texte'), ('3', 'Présentation'), ('4', 'Image'), ('5', 'PDF');
INSERT INTO `hn_fichier_extension` (`ext_id`, `fictyp_id`, `ext_valeur`) VALUES (NULL, '1', 'ods'), (NULL, '1', 'xls'), (NULL, '1', 'xslx'), (NULL, '2', 'odt'), (NULL, '2', 'doc'), (NULL, '2', 'docx'), (NULL, '2', 'dot'), (NULL, '3', 'odp'), (NULL, '3', 'ppt'), (NULL, '3', 'pptx'), (NULL, '4', 'png'), (NULL, '4', 'jpg'), (NULL, '4', 'jpeg'), (NULL, '4', 'gif'), (NULL, '5', 'pdf');
