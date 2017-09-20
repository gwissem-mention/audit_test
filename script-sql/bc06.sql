CREATE TABLE hn_communautepratique_groupe_domain (group_id INT UNSIGNED NOT NULL, dom_id INT NOT NULL, INDEX IDX_F07BC6D6FE54D947 (group_id), INDEX IDX_F07BC6D669893F8F (dom_id), PRIMARY KEY(group_id, dom_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_groupe_domain ADD CONSTRAINT FK_F07BC6D6FE54D947 FOREIGN KEY (group_id) REFERENCES hn_communautepratique_groupe (group_id);
ALTER TABLE hn_communautepratique_groupe_domain ADD CONSTRAINT FK_F07BC6D669893F8F FOREIGN KEY (dom_id) REFERENCES hn_domaine (dom_id);

INSERT INTO hn_communautepratique_groupe_domain (group_id, dom_id) SELECT group_id, dom_id FROM hn_communautepratique_groupe;

ALTER TABLE hn_communautepratique_groupe DROP FOREIGN KEY FK_A34AA84569893F8F;
DROP INDEX IDX_A34AA84569893F8F ON hn_communautepratique_groupe;
ALTER TABLE hn_communautepratique_groupe DROP dom_id;


