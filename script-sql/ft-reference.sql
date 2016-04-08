#<-- !!! Avant un d:s:u --force

# Parents des références
CREATE TABLE hn_reference_has_parent (ref_parent_id INT NOT NULL COMMENT 'ID de la référence', ref_id INT NOT NULL COMMENT 'ID de la référence', INDEX IDX_4522B66B120CB35 (ref_parent_id), INDEX IDX_4522B66B21B741A9 (ref_id), PRIMARY KEY(ref_parent_id, ref_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
INSERT INTO hn_reference_has_parent(ref_parent_id, ref_id) (SELECT parent_id, ref_id FROM `hn_reference` WHERE parent_id IS NOT NULL);

ALTER TABLE hn_reference ADD ref_parentable TINYINT(1) DEFAULT '0' NOT NULL COMMENT 'Si la référence peut être parent';
UPDATE hn_reference SET ref_parentable = 1 WHERE ref_id IN (SELECT ref_parent_id FROM hn_reference_has_parent);


ALTER TABLE hn_reference ADD ref_in_recherche TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE hn_reference ADD ref_reference TINYINT(1) DEFAULT '0' NOT NULL;
UPDATE hn_reference SET ref_in_recherche = ref_recherche, ref_reference = ref_dictionnaire;

#-->

# Modif menu libellés
UPDATE `core_menu_item` SET `itm_name` = 'Erreurs dans les URL' WHERE `core_menu_item`.`itm_id` = 177;
UPDATE `core_menu_item` SET `itm_name` = 'Dictionnaire' WHERE `core_menu_item`.`itm_id` = 17 ;
UPDATE `core_menu_item` SET `itm_name` = 'Ajouter un concept' WHERE `core_menu_item`.`itm_id` = 35;
UPDATE `core_menu_item` SET `itm_name` = 'Fiche concept' WHERE `core_menu_item`.`itm_id` = 36;
UPDATE `core_menu_item` SET `itm_name` = 'Editer un concept' WHERE `core_menu_item`.`itm_id` = 37;

# Nv menu RSS en pied de page
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, NULL, '2', 'RSS', 'hopitalnumerique_objet_objet_feed_rss', '[]', '0', NULL, NULL, '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '6');

# Courriel de recommandation
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`, `mail_notification_region_referent`) VALUES (63, 'Recommandation', 'Recommandation', '', '', 'Bonjour, Je te recommande cette page : %url. A+.', '', '0');

#<-- Glossaire
# Menu Glossaire en pied de page
UPDATE `core_menu_item` SET `itm_route` = 'hopitalnumerique_reference_glossaire_list' WHERE `core_menu_item`.`itm_id` = 186;
#-->
