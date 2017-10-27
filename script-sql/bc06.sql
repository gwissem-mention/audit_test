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


/** Discussion **/
CREATE TABLE hn_communautepratique_discussion (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, public TINYINT(1) NOT NULL, recommended TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, INDEX IDX_3C9AF352A76ED395 (user_id), INDEX IDX_3C9AF352727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE hn_communautepratique_discussion_group (discussion_id INT NOT NULL, groupe_group_id INT UNSIGNED NOT NULL, INDEX IDX_56A6361C1ADED311 (discussion_id), INDEX IDX_56A6361C6FF6D237 (groupe_group_id), PRIMARY KEY(discussion_id, groupe_group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_discussion ADD CONSTRAINT FK_3C9AF352A76ED395 FOREIGN KEY (user_id) REFERENCES core_user (usr_id);
ALTER TABLE hn_communautepratique_discussion ADD CONSTRAINT FK_3C9AF352727ACA70 FOREIGN KEY (parent_id) REFERENCES hn_communautepratique_discussion (id);
ALTER TABLE hn_communautepratique_discussion_group ADD CONSTRAINT FK_56A6361C1ADED311 FOREIGN KEY (discussion_id) REFERENCES hn_communautepratique_discussion (id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_discussion_group ADD CONSTRAINT FK_56A6361C6FF6D237 FOREIGN KEY (groupe_group_id) REFERENCES hn_communautepratique_groupe (group_id);

/** Message **/
CREATE TABLE hn_communautepratique_discussion_message (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, discussion_id INT DEFAULT NULL, content LONGTEXT NOT NULL, published TINYINT(1) NOT NULL, helpful TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, INDEX IDX_584C655FA76ED395 (user_id), INDEX IDX_584C655F1ADED311 (discussion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_discussion_message ADD CONSTRAINT FK_584C655FA76ED395 FOREIGN KEY (user_id) REFERENCES core_user (usr_id);
ALTER TABLE hn_communautepratique_discussion_message ADD CONSTRAINT FK_584C655F1ADED311 FOREIGN KEY (discussion_id) REFERENCES hn_communautepratique_discussion (id);

/** Read **/
CREATE TABLE hn_communautepratique_discussion_read (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, discussion_id INT DEFAULT NULL, lastMessageDate DATETIME NOT NULL, INDEX IDX_90055047A76ED395 (user_id), INDEX IDX_900550471ADED311 (discussion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_discussion_read ADD CONSTRAINT FK_90055047A76ED395 FOREIGN KEY (user_id) REFERENCES core_user (usr_id);
ALTER TABLE hn_communautepratique_discussion_read ADD CONSTRAINT FK_900550471ADED311 FOREIGN KEY (discussion_id) REFERENCES hn_communautepratique_discussion (id);

/** Presentation discussion **/
ALTER TABLE hn_communautepratique_groupe ADD presentationDiscussion_id INT DEFAULT NULL;
ALTER TABLE hn_communautepratique_groupe ADD CONSTRAINT FK_A34AA845CFB42D94 FOREIGN KEY (presentationDiscussion_id) REFERENCES hn_communautepratique_discussion (id);
CREATE UNIQUE INDEX UNIQ_A34AA845CFB42D94 ON hn_communautepratique_groupe (presentationDiscussion_id);

/** Group domains **/
CREATE TABLE hn_communautepratique_discussion_domain (discussion_id INT NOT NULL, domaine_dom_id INT NOT NULL, INDEX IDX_589D12091ADED311 (discussion_id), INDEX IDX_589D120966608EA1 (domaine_dom_id), PRIMARY KEY(discussion_id, domaine_dom_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_discussion_domain ADD CONSTRAINT FK_589D12091ADED311 FOREIGN KEY (discussion_id) REFERENCES hn_communautepratique_discussion (id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_discussion_domain ADD CONSTRAINT FK_589D120966608EA1 FOREIGN KEY (domaine_dom_id) REFERENCES hn_domaine (dom_id);

ALTER TABLE hn_communautepratique_discussion DROP FOREIGN KEY FK_3C9AF352727ACA70;
ALTER TABLE hn_communautepratique_discussion ADD CONSTRAINT FK_3C9AF352727ACA70 FOREIGN KEY (parent_id) REFERENCES hn_communautepratique_discussion (id) ON DELETE SET NULL;


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

CREATE TABLE object_identity_subscription (user_id INT NOT NULL, subscribedAt DATETIME NOT NULL, objectIdentity_id VARCHAR(255) NOT NULL, INDEX IDX_A855FE45697602BB (objectIdentity_id), INDEX IDX_A855FE45A76ED395 (user_id), PRIMARY KEY(objectIdentity_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE object_identity_subscription ADD CONSTRAINT FK_A855FE45697602BB FOREIGN KEY (objectIdentity_id) REFERENCES object_identity (id) ON DELETE CASCADE;
ALTER TABLE object_identity_subscription ADD CONSTRAINT FK_A855FE45A76ED395 FOREIGN KEY (user_id) REFERENCES core_user (usr_id) ON DELETE CASCADE;


CREATE TABLE hn_file (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_5302C07E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE hn_communautepratique_discussion_message_file (message_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_6CE3BAD8537A1329 (message_id), INDEX IDX_6CE3BAD893CB796C (file_id), PRIMARY KEY(message_id, file_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_file ADD CONSTRAINT FK_5302C07E3C61F9 FOREIGN KEY (owner_id) REFERENCES core_user (usr_id);
ALTER TABLE hn_communautepratique_discussion_message_file ADD CONSTRAINT FK_6CE3BAD8537A1329 FOREIGN KEY (message_id) REFERENCES hn_communautepratique_discussion_message (id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_discussion_message_file ADD CONSTRAINT FK_6CE3BAD893CB796C FOREIGN KEY (file_id) REFERENCES hn_file (id) ON DELETE CASCADE;
ALTER TABLE hn_file ADD clientName VARCHAR(255) NOT NULL;


INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_display_children, itm_role, itm_order, itm_display) VALUES (273, 3, 'Actualité de la communauté', 'hopitalnumerique_communautepratique_news_index', 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 0, 1);
UPDATE core_menu_item SET itm_name = 'Espace de discussion', itm_route_parameters = NULL, itm_route = 'hopitalnumerique_communautepratique_discussions_public' WHERE itm_route = 'ccdn_forum_user_category_index' AND itm_name LIKE '%espace%' AND itm_parent = 273;
UPDATE core_menu_item SET itm_route = NULL, itm_route_parameters = NULL WHERE itm_route = 'hopitalnumerique_communautepratique_accueil_index' AND itm_parent IS NULL;

UPDATE core_menu_item SET itm_name = 'Groupes d\'entre-aide' ,itm_route = 'hopitalnumerique_communautepratique_groupe_list' WHERE itm_route = 'hopitalnumerique_communautepratique_accueil_index' AND mnu_menu = 3;
INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_display_children, itm_role, itm_order, itm_display) VALUE (273, 3, 'Annuaire de la communauté', 'hopitalnumerique_communautepratique_user_list', 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3, 1);
DELETE FROM core_menu_item WHERE itm_id = 277;


UPDATE core_user SET usr_presentation = CONCAT(usr_presentation, CHAR(13), CHAR(10), CHAR(13), CHAR(10), usr_biographie) WHERE usr_biographie IS NOT NULL AND usr_presentation IS NOT NULL;
UPDATE core_user SET usr_presentation = usr_biographie WHERE usr_biographie IS NOT NULL AND usr_presentation IS NULL;
ALTER TABLE core_user DROP usr_biographie;


INSERT INTO core_mail (mail_id, mail_objet, mail_description, mail_expediteur_mail, mail_expediteur_name, mail_body, mail_params, mail_notification_region_referent) VALUES (81, '[%subjectDomaine] - Nouvelle réponse dans la communauté de pratique', 'Réponse dans une discussion de la communauté de pratique', '%mailContactDomaineCurrent', 'ANAP - %nomContactDomaineCurrent', 'Bonjour,

%nomUtilisateur %prenomUtilisateur vient de poster une réponse dans la discussion "%discussionName" :
%urlDiscussion

Cordialement,', '{"%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion"}', 0);
