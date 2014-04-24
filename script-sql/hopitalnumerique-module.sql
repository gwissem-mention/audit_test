/* ---- Liens de menu ---- */
/* Ajout */

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(125, NULL, 1, 'Gestion des modules', 'hopitalnumerique_module_module', '[]', 0, NULL, 'fa fa-adjust', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
(126, 125, 1, 'Ajouter un module', 'hopitalnumerique_module_module_add', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3),
(127, 125, 1, 'Fiche d''un module', 'hopitalnumerique_module_module_show', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2),
(128, 125, 1, 'Editer un module', 'hopitalnumerique_module_module_edit', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(129, 125, 1, 'Liste des sessions d''un module', 'hopitalnumerique_module_module_session', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4),
(130, 125, 1, 'Ajouter une session à un module', 'hopitalnumerique_module_module_session_add', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 5),
(131, 125, 1, 'Editer une session d''un module', 'hopitalnumerique_module_module_session_edit', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 6),
(132, 125, 1, 'Afficher une session d''un module', 'hopitalnumerique_module_module_session_show', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
(133, 125, 1, 'Listes des inscriptions d''une session d''un module', 'hopitalnumerique_module_module_session_inscription', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 8);

/* Suppression */
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 125;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 126;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 127;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 128;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 129;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 130;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 131;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 132;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 133;

/* ---- Reference ----*/
UPDATE `hn_reference` SET `ref_libelle` = 'Actif' WHERE `hn_reference`.`ref_id` = 328;
UPDATE `hn_reference` SET `ref_libelle` = 'Inactif' WHERE `hn_reference`.`ref_id` = 329;
UPDATE `hn_reference` SET `ref_libelle` = 'Annulé' WHERE `hn_reference`.`ref_id` = 330;