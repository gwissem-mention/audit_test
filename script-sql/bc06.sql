CREATE TABLE hn_communautepratique_groupe_domain (group_id INT UNSIGNED NOT NULL, dom_id INT NOT NULL, INDEX IDX_F07BC6D6FE54D947 (group_id), INDEX IDX_F07BC6D669893F8F (dom_id), PRIMARY KEY(group_id, dom_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_groupe_domain ADD CONSTRAINT FK_F07BC6D669893F8F FOREIGN KEY (dom_id) REFERENCES hn_domaine (dom_id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_groupe_domain ADD CONSTRAINT FK_F07BC6D6FE54D947 FOREIGN KEY (group_id) REFERENCES hn_communautepratique_groupe (group_id) ON DELETE CASCADE;

INSERT INTO hn_communautepratique_groupe_domain (group_id, dom_id) SELECT group_id, dom_id FROM hn_communautepratique_groupe;

ALTER TABLE hn_communautepratique_groupe DROP FOREIGN KEY FK_A34AA84569893F8F;
DROP INDEX IDX_A34AA84569893F8F ON hn_communautepratique_groupe;
ALTER TABLE hn_communautepratique_groupe DROP dom_id;


CREATE TABLE hn_communautepratique_groupe_role (group_id INT UNSIGNED NOT NULL, ro_id INT NOT NULL COMMENT 'ID du groupe', INDEX IDX_EAA7FB9EFE54D947 (group_id), INDEX IDX_EAA7FB9EBF75BFC5 (ro_id), PRIMARY KEY(group_id, ro_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_groupe_role ADD CONSTRAINT FK_EAA7FB9EFE54D947 FOREIGN KEY (group_id) REFERENCES hn_communautepratique_groupe (group_id);
ALTER TABLE hn_communautepratique_groupe_role ADD CONSTRAINT FK_EAA7FB9EBF75BFC5 FOREIGN KEY (ro_id) REFERENCES core_role (ro_id);

CREATE TABLE hn_communautepratique_viewed_member (member_id INT NOT NULL, viewer_id INT NOT NULL, viewedAt DATETIME NOT NULL, INDEX IDX_FF8A9B11A76ED395 (member_id), INDEX IDX_FF8A9B116C59C752 (viewer_id), PRIMARY KEY(member_id, viewer_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_viewed_member ADD CONSTRAINT FK_FF8A9B11A76ED395 FOREIGN KEY (member_id) REFERENCES core_user (usr_id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_viewed_member ADD CONSTRAINT FK_FF8A9B116C59C752 FOREIGN KEY (viewer_id) REFERENCES core_user (usr_id) ON DELETE CASCADE;

ALTER TABLE core_user ADD communautePratiqueEnrollmentDate DATETIME DEFAULT NULL;

ALTER TABLE hn_communautepratique_viewed_member DROP FOREIGN KEY FK_FF8A9B11A76ED395;
DROP INDEX idx_ff8a9b11a76ed395 ON hn_communautepratique_viewed_member;
CREATE INDEX IDX_FF8A9B117597D3FE ON hn_communautepratique_viewed_member (member_id);
ALTER TABLE hn_communautepratique_viewed_member ADD CONSTRAINT FK_FF8A9B11A76ED395 FOREIGN KEY (member_id) REFERENCES core_user (usr_id) ON DELETE CASCADE;


INSERT INTO hn_reference (ref_id, ref_libelle, ref_etat, ref_lock, ref_order) VALUES (4000, 'Discussion de la communauté de pratique', 3, 0, 1);
INSERT INTO hn_reference_code (reference, label) VALUES (4000, 'CATEGORIE_OBJET');

ALTER TABLE hn_communautepratique_discussion ADD object_id INT DEFAULT NULL COMMENT 'ID de l objet';
ALTER TABLE hn_communautepratique_discussion ADD CONSTRAINT FK_3C9AF352232D562B FOREIGN KEY (object_id) REFERENCES hn_objet (obj_id) ON DELETE SET NULL;
CREATE INDEX IDX_3C9AF352232D562B ON hn_communautepratique_discussion (object_id);

ALTER TABLE hn_communautepratique_discussion DROP FOREIGN KEY FK_3C9AF352232D562B;
DROP INDEX IDX_3C9AF352232D562B ON hn_communautepratique_discussion;
ALTER TABLE hn_communautepratique_discussion CHANGE object_id relatedObject_id INT DEFAULT NULL COMMENT 'ID de l objet';
ALTER TABLE hn_communautepratique_discussion ADD CONSTRAINT FK_3C9AF352299FDF45 FOREIGN KEY (relatedObject_id) REFERENCES hn_objet (obj_id) ON DELETE SET NULL;
CREATE INDEX IDX_3C9AF352299FDF45 ON hn_communautepratique_discussion (relatedObject_id);


/** ObjectIdentity stuffs **/
CREATE TABLE object_identity_relation (id INT AUTO_INCREMENT NOT NULL, `order` INT NOT NULL, sourceObjectIdentity_id VARCHAR(255) DEFAULT NULL, targetObjectIdentity_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_D278C1C1BF396750 (id), INDEX IDX_D278C1C179608353 (sourceObjectIdentity_id), INDEX IDX_D278C1C13AED7BF5 (targetObjectIdentity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE object_identity (id VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, objectId VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BC4304ACBF396750 (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE object_identity_relation ADD CONSTRAINT FK_D278C1C179608353 FOREIGN KEY (sourceObjectIdentity_id) REFERENCES object_identity (id);
ALTER TABLE object_identity_relation ADD CONSTRAINT FK_D278C1C13AED7BF5 FOREIGN KEY (targetObjectIdentity_id) REFERENCES object_identity (id);
ALTER TABLE object_identity_relation CHANGE `order` position INT NOT NULL;

ALTER TABLE object_identity_relation DROP FOREIGN KEY FK_D278C1C13AED7BF5;
ALTER TABLE object_identity_relation DROP FOREIGN KEY FK_D278C1C179608353;
ALTER TABLE object_identity_relation ADD CONSTRAINT FK_D278C1C13AED7BF5 FOREIGN KEY (targetObjectIdentity_id) REFERENCES object_identity (id) ON DELETE CASCADE;
ALTER TABLE object_identity_relation ADD CONSTRAINT FK_D278C1C179608353 FOREIGN KEY (sourceObjectIdentity_id) REFERENCES object_identity (id) ON DELETE CASCADE;
