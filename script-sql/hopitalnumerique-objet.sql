ALTER TABLE hn_objet ADD cp_group_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE hn_objet ADD CONSTRAINT FK_C09252D862654CC4 FOREIGN KEY (cp_group_id) REFERENCES hn_communautepratique_groupe (group_id);
CREATE INDEX IDX_C09252D862654CC4 ON hn_objet (cp_group_id);
