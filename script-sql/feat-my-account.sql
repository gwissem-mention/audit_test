-- Civilité

DELETE FROM `hn_reference` WHERE `ref_id` IN ('8','9');

-- Reference LOGICIELS

INSERT INTO `hn_reference` (`ref_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_lock`, `ref_order`, `ref_image`, `ref_in_recherche`, `ref_reference`, `ref_all_domaines`, `ref_reference_libelle`, `ref_in_glossaire`, `ref_sigle`, `ref_glossaire_libelle`, `ref_description_courte`, `ref_casse_sensible`, `ref_description_longue`)
VALUES
  (null, 'Powerpoint', 'LOGICIELS', 3, 0, 1, NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, 0, NULL),
  (null, 'Excel', 'LOGICIELS', 3, 0, 1, NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, 0, NULL),
  (null, 'Word', 'LOGICIELS', 3, 0, 1, NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, 0, NULL);

-- Textes administrables

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (21, 'Module_moncompte_competences', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (21, 1),
  (21, 2),
  (21, 3),
  (21, 4),
  (21, 5),
  (21, 7),
  (21, 8),
  (21, 9),
  (21, 10),
  (21, 11),
  (21, 12),
  (21, 13),
  (21, 14),
  (21, 15),
  (21, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (22, 'Module_moncompte_structure', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (22, 1),
  (22, 2),
  (22, 3),
  (22, 4),
  (22, 5),
  (22, 7),
  (22, 8),
  (22, 9),
  (22, 10),
  (22, 11),
  (22, 12),
  (22, 13),
  (22, 14),
  (22, 15),
  (22, 17);


-- MENU --

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `core_menu_item` WHERE `itm_parent` IN (104, 73, 68, 248, 249, 250, 342, 355, 368, 372, 376, 380, 386, 397);

UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '68';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '248';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '249';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '250';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '342';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '355';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '368';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '372';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '376';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '380';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '386';
UPDATE `core_menu_item` SET `itm_route` = 'account_dashboard' WHERE `itm_id` = '397';

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '68', '3', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '68', '3', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '68', '3', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '68', '3', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '248', '4', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '248', '4', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '248', '4', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '248', '4', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '249', '5', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '249', '5', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '249', '5', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '249', '5', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '250', '6', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '250', '6', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '250', '6', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '250', '6', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '342', '8', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '342', '8', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '342', '8', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '342', '8', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '355', '14', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '355', '14', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '355', '14', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '355', '14', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '368', '9', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '368', '9', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '368', '9', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '368', '9', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '372', '12', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '372', '12', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '372', '12', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '372', '12', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '376', '10', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '376', '10', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '376', '10', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '376', '10', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '380', '13', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '380', '13', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '380', '13', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '380', '13', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '386', '11', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '386', '11', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '386', '11', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '386', '11', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '397', '15', 'Mon profil', 'account_profile', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '397', '15', 'Mes services', 'account_service', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '2');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '397', '15', 'Mon panier', 'account_cart', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '397', '15', 'Mes paramètres', 'account_parameter', '[]', '0', NULL, NULL, '1', '1', 'IS_AUTHENTICATED_ANONYMOUSLY', '4');

SET FOREIGN_KEY_CHECKS = 1;



UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/$/' WHERE res_id = 11;

UPDATE `core_ressource` SET res_pattern = '/^\\/(mon-compte\\/requetes)|(requetes)/' WHERE res_id = 14;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/intervention\\/demande\\/nouveau/' WHERE res_id = 15;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/(expert|questionnaire)/' WHERE res_id = 16;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/(ambassadeur|questionnaire)/' WHERE res_id = 17;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/(informations-personnelles|mot-de-passe|desinscription)/' WHERE res_id = 18;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/inscription/' WHERE res_id = 19;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/intervention/' WHERE res_id = 20;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/suivi-des-paiements/' WHERE res_id = 28;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/module-thematiques/' WHERE res_id = 31;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/autodiagnostic/' WHERE res_id = 45;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/acces-compte/' WHERE res_id = 52;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/suivi-activite/' WHERE res_id = 54;

UPDATE `core_ressource` SET res_pattern = '/^\\/mon-compte\\/informations-manquantes/' WHERE res_id = 66;

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (76, 'FrontOffice - Mon profil', '/^\\/mon-compte\\/mon-profil/', '1', '2');
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (77, 'FrontOffice - Mes services', '/^\\/mon-compte\\/mes-services/', '2', '2');
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (78, 'FrontOffice - Mon panier', '/^\\/mon-compte\\/mon-panier/', '3', '2');
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (79, 'FrontOffice - Mes paramètres', '/^\\/mon-compte\\/mes-parametres/', '4', '2');
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (80, 'FrontOffice - Liste des départements', '/^\\/ajax-load\\/departements/', '1', '2');

INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('1', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('2', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('3', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('4', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('5', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('6', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('7', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('8', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('9', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('11', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('100', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('101', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('103', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('105', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('106', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('107', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('108', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('109', '76', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('110', '76', '1', '1');

INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('1', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('2', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('3', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('4', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('5', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('6', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('7', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('8', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('9', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('11', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('100', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('101', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('103', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('105', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('106', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('107', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('108', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('109', '77', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('110', '77', '1', '1');

INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('1', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('2', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('3', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('4', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('5', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('6', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('7', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('8', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('9', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('11', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('100', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('101', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('103', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('105', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('106', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('107', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('108', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('109', '78', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('110', '78', '1', '1');

INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('1', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('2', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('3', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('4', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('5', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('6', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('7', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('8', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('9', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('11', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('100', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('101', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('103', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('105', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('106', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('107', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('108', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('109', '79', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('110', '79', '1', '1');

INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('1', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('2', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('3', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('4', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('5', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('6', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('7', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('8', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('9', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('10', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('11', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('100', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('101', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('103', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('105', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('106', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('107', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('108', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('109', '80', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('110', '80', '1', '1');

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (81, 'FrontOffice - Divers panier', '/^\\/cart/', '4', '2');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`)
VALUES
	(108, 81, 1, 1),
	(3, 81, 1, 1),
	(10, 81, 1, 1),
	(9, 81, 1, 1),
	(11, 81, 1, 1),
	(105, 81, 1, 1),
	(5, 81, 1, 1),
	(106, 81, 1, 1),
	(111, 81, 1, 1),
	(100, 81, 1, 1),
	(8, 81, 1, 1),
	(7, 81, 1, 1),
	(107, 81, 1, 1),
	(101, 81, 1, 1),
	(103, 81, 1, 1),
	(110, 81, 1, 1),
	(2, 81, 1, 1),
	(4, 81, 1, 1),
	(109, 81, 1, 1),
	(6, 81, 1, 1);

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (82, 'FrontOffice - Popin référencement utilisateur', '/^\\/user-referencement/', '1', '2');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`)
VALUES
(1, 82, 1, 1),
(2, 82, 1, 1),
(3, 82, 1, 1),
(4, 82, 1, 1),
(5, 82, 1, 1),
(6, 82, 1, 1),
(7, 82, 1, 1),
(8, 82, 1, 1),
(9, 82, 1, 1),
(11, 82, 1, 1),
(100, 82, 1, 1),
(101, 82, 1, 1),
(103, 82, 1, 1),
(105, 82, 1, 1),
(106, 82, 1, 1),
(107, 82, 1, 1),
(108, 82, 1, 1),
(109, 82, 1, 1),
(110, 82, 1, 1);

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (83, 'FrontOffice - Sauvegarde référencement', '/^\\/referencement\\/popin\\/\\d+\\/\\d+\\/savechosenreferences/', '0', '2');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`)
VALUES
  (1, 83, 1, 1),
  (2, 83, 1, 1),
  (3, 83, 1, 1),
  (4, 83, 1, 1),
  (5, 83, 1, 1),
  (6, 83, 1, 1),
  (7, 83, 1, 1),
  (8, 83, 1, 1),
  (9, 83, 1, 1),
  (11, 83, 1, 1),
  (100, 83, 1, 1),
  (101, 83, 1, 1),
  (103, 83, 1, 1),
  (105, 83, 1, 1),
  (106, 83, 1, 1),
  (107, 83, 1, 1),
  (108, 83, 1, 1),
  (109, 83, 1, 1),
  (110, 83, 1, 1);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (23, 'Module_moncompte_services_recherches', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (23, 1),
  (23, 2),
  (23, 3),
  (23, 4),
  (23, 5),
  (23, 7),
  (23, 8),
  (23, 9),
  (23, 10),
  (23, 11),
  (23, 12),
  (23, 13),
  (23, 14),
  (23, 15),
  (23, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (24, 'Module_moncompte_services_publications', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (24, 1),
  (24, 2),
  (24, 3),
  (24, 4),
  (24, 5),
  (24, 7),
  (24, 8),
  (24, 9),
  (24, 10),
  (24, 11),
  (24, 12),
  (24, 13),
  (24, 14),
  (24, 15),
  (24, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (25, 'Module_moncompte_services_autodiagnostics', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (25, 1),
  (25, 2),
  (25, 3),
  (25, 4),
  (25, 5),
  (25, 7),
  (25, 8),
  (25, 9),
  (25, 10),
  (25, 11),
  (25, 12),
  (25, 13),
  (25, 14),
  (25, 15),
  (25, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (26, 'Module_moncompte_services_parcours', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (26, 1),
  (26, 2),
  (26, 3),
  (26, 4),
  (26, 5),
  (26, 7),
  (26, 8),
  (26, 9),
  (26, 10),
  (26, 11),
  (26, 12),
  (26, 13),
  (26, 14),
  (26, 15),
  (26, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (27, 'Module_moncompte_services_questionnaires', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (27, 1),
  (27, 2),
  (27, 3),
  (27, 4),
  (27, 5),
  (27, 7),
  (27, 8),
  (27, 9),
  (27, 10),
  (27, 11),
  (27, 12),
  (27, 13),
  (27, 14),
  (27, 15),
  (27, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (28, 'Module_moncompte_services_sessions', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (28, 1),
  (28, 2),
  (28, 3),
  (28, 4),
  (28, 5),
  (28, 7),
  (28, 8),
  (28, 9),
  (28, 10),
  (28, 11),
  (28, 12),
  (28, 13),
  (28, 14),
  (28, 15),
  (28, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (29, 'Module_moncompte_services_interventions', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (29, 1),
  (29, 2),
  (29, 3),
  (29, 4),
  (29, 5),
  (29, 7),
  (29, 8),
  (29, 9),
  (29, 10),
  (29, 11),
  (29, 12),
  (29, 13),
  (29, 14),
  (29, 15),
  (29, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (30, 'Module_moncompte_services_introduction', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (30, 1),
  (30, 2),
  (30, 3),
  (30, 4),
  (30, 5),
  (30, 7),
  (30, 8),
  (30, 9),
  (30, 10),
  (30, 11),
  (30, 12),
  (30, 13),
  (30, 14),
  (30, 15),
  (30, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (31, 'Module_moncompte_services_sessions_inscription', '<p>Inscription Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (31, 1),
  (31, 2),
  (31, 3),
  (31, 4),
  (31, 5),
  (31, 7),
  (31, 8),
  (31, 9),
  (31, 10),
  (31, 11),
  (31, 12),
  (31, 13),
  (31, 14),
  (31, 15),
  (31, 17);

INSERT INTO `core_textedynamique_code` (`txt_id`, `txt_code`, `txt_texte`)
VALUES
  (32, 'Module_moncompte_services_interventions_nouvelles', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut culpa deserunt dolor dolorem ea fugiat impedit ipsam ipsum itaque labore libero molestias, non optio placeat recusandae repudiandae soluta tenetur voluptate.</p>');

INSERT INTO `core_textedynamique_code_domaine` (`txt_id`, `dom_id`)
VALUES
  (32, 1),
  (32, 2),
  (32, 3),
  (32, 4),
  (32, 5),
  (32, 7),
  (32, 8),
  (32, 9),
  (32, 10),
  (32, 11),
  (32, 12),
  (32, 13),
  (32, 14),
  (32, 15),
  (32, 17);


UPDATE hn_objet_consultation set viewsCount = 1;
