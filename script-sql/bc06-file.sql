CREATE TABLE hn_file (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_5302C07E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE hn_communautepratique_discussion_message_file (message_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_6CE3BAD8537A1329 (message_id), INDEX IDX_6CE3BAD893CB796C (file_id), PRIMARY KEY(message_id, file_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_file ADD CONSTRAINT FK_5302C07E3C61F9 FOREIGN KEY (owner_id) REFERENCES core_user (usr_id);
ALTER TABLE hn_communautepratique_discussion_message_file ADD CONSTRAINT FK_6CE3BAD8537A1329 FOREIGN KEY (message_id) REFERENCES hn_communautepratique_discussion_message (id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_discussion_message_file ADD CONSTRAINT FK_6CE3BAD893CB796C FOREIGN KEY (file_id) REFERENCES hn_file (id) ON DELETE CASCADE;
ALTER TABLE hn_file ADD clientName VARCHAR(255) NOT NULL;
