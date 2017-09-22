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

/** Grou domains **/
CREATE TABLE hn_communautepratique_discussion_domain (discussion_id INT NOT NULL, domaine_dom_id INT NOT NULL, INDEX IDX_589D12091ADED311 (discussion_id), INDEX IDX_589D120966608EA1 (domaine_dom_id), PRIMARY KEY(discussion_id, domaine_dom_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_discussion_domain ADD CONSTRAINT FK_589D12091ADED311 FOREIGN KEY (discussion_id) REFERENCES hn_communautepratique_discussion (id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_discussion_domain ADD CONSTRAINT FK_589D120966608EA1 FOREIGN KEY (domaine_dom_id) REFERENCES hn_domaine (dom_id);
