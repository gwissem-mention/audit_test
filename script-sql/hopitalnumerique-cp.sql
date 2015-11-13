/* <-- Création des tables */
CREATE TABLE hn_communautepratique_groupe (group_id INT UNSIGNED AUTO_INCREMENT NOT NULL, dom_id INT NOT NULL, qst_id INT NOT NULL COMMENT 'ID du questionnaire', group_titre VARCHAR(255) NOT NULL, group_description_courte TEXT NOT NULL, group_description_html TEXT NOT NULL, group_nombre_participants_maximum SMALLINT UNSIGNED NOT NULL, group_date_inscription_ouverture DATE NOT NULL, group_date_demarrage DATE NOT NULL, group_date_fin DATE NOT NULL, group_vedette TINYINT(1) DEFAULT '0' NOT NULL, INDEX IDX_A34AA84569893F8F (dom_id), INDEX IDX_A34AA845B293CE31 (qst_id), PRIMARY KEY(group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE hn_communautepratique_groupe_animateur (group_id INT UNSIGNED NOT NULL, usr_id INT NOT NULL COMMENT 'ID de l utilisateur', INDEX IDX_840981F7FE54D947 (group_id), INDEX IDX_840981F7C69D3FB (usr_id), PRIMARY KEY(group_id, usr_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE hn_communautepratique_groupe ADD CONSTRAINT FK_A34AA84569893F8F FOREIGN KEY (dom_id) REFERENCES hn_domaine (dom_id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_groupe ADD CONSTRAINT FK_A34AA845B293CE31 FOREIGN KEY (qst_id) REFERENCES hn_questionnaire_questionnaire (qst_id) ON DELETE CASCADE;
ALTER TABLE hn_communautepratique_groupe_animateur ADD CONSTRAINT FK_840981F7FE54D947 FOREIGN KEY (group_id) REFERENCES hn_communautepratique_groupe (group_id);
ALTER TABLE hn_communautepratique_groupe_animateur ADD CONSTRAINT FK_840981F7C69D3FB FOREIGN KEY (usr_id) REFERENCES core_user (usr_id);
ALTER TABLE hn_communautepratique_groupe ADD group_actif TINYINT(1) DEFAULT '0' NOT NULL;
/* --> */

/* Droits */
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (NULL, 'BackOffice - Gestion de la communauté de pratique', '/^\\/admin\\/communaute\\-de\\-pratique/', '38', '2');

/* Menu */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES ('253', NULL, '1', 'Communauté de pratique', 'hopitalnumerique_communautepratique_admin_groupe_list', '[]', '0', NULL, 'fa fa-users', '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '15');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES ('254', '253', '1', 'Éditer un groupe', 'hopitalnumerique_communautepratique_admin_groupe_edit', NULL, '0', NULL, NULL, '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES ('255', '253', '1', 'Ajouter un groupe', 'hopitalnumerique_communautepratique_admin_groupe_add', NULL, '0', NULL, NULL, '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');

