/* ---- Liens de menu ---- */
/* Ajout */

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(128, NULL, 1, 'Gestion des modules', 'hopitalnumerique_module_module', '[]', 0, NULL, 'fa fa-adjust', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
(129, 128, 1, 'Ajouter un module', 'hopitalnumerique_module_module_add', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3),
(130, 128, 1, 'Fiche d''un module', 'hopitalnumerique_module_module_show', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2),
(131, 128, 1, 'Editer un module', 'hopitalnumerique_module_module_edit', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(132, 128, 1, 'Liste des sessions d''un module', 'hopitalnumerique_module_module_session', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4),
(133, 128, 1, 'Ajouter une session à un module', 'hopitalnumerique_module_module_session_add', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 5),
(134, 128, 1, 'Editer une session d''un module', 'hopitalnumerique_module_module_session_edit', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 6),
(135, 128, 1, 'Afficher une session d''un module', 'hopitalnumerique_module_module_session_show', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
(136, 128, 1, 'Listes des inscriptions d''une session d''un module', 'hopitalnumerique_module_module_session_inscription', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 8);

/* Suppression */
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 128;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 129;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 130;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 131;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 132;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 133;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 134;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 135;
DELETE FROM `core_menu_item` WHERE `core_menu_item`.`itm_id` = 136;

/* ---- Reference ----*/
/* En cas de modifs : */
/* 13:35:43 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '400' WHERE `ref_id` = '325';
/* 13:36:14 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '401' WHERE `ref_id` = '326';
/* 13:36:21 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '402' WHERE `ref_id` = '327';
/* 13:37:10 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '403' WHERE `ref_id` = '328';
/* 13:37:16 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '404' WHERE `ref_id` = '329';
/* 13:37:21 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '405' WHERE `ref_id` = '330';
/* 13:37:26 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '406' WHERE `ref_id` = '331';
/* 13:37:30 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '407' WHERE `ref_id` = '332';
/* 13:37:32 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '408' WHERE `ref_id` = '333';
/* 13:37:35 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '409' WHERE `ref_id` = '334';
/* 13:37:38 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '410' WHERE `ref_id` = '335';
/* 13:37:42 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '411' WHERE `ref_id` = '336';
/* 13:37:45 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '412' WHERE `ref_id` = '337';
/* 13:37:48 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '413' WHERE `ref_id` = '338';
/* 13:37:52 Gaia (localnodevo) */ UPDATE `hn_reference` SET `ref_id` = '414' WHERE `ref_id` = '339';

/* en cas d'ajout : */
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (400, NULL, 'Durée formation 1', 'DUREE_FORMATION', 3, 0, 0, 1, 1),
    (401, NULL, 'Durée formation 2', 'DUREE_FORMATION', 3, 0, 0, 1, 2),
    (402, NULL, 'Durée formation 3', 'DUREE_FORMATION', 3, 0, 0, 1, 3),
    (403, NULL, 'Actif', 'STATUT_SESSION_FORMATION', 3, 0, 0, 1, 1),
    (404, NULL, 'Inactif', 'STATUT_SESSION_FORMATION', 3, 0, 0, 1, 2),
    (405, NULL, 'Annulé', 'STATUT_SESSION_FORMATION', 3, 0, 0, 1, 3),
    (406, NULL, 'En attente', 'STATUT_FORMATION', 3, 0, 0, 1, 1),
    (407, NULL, 'Acceptée', 'STATUT_FORMATION', 3, 0, 0, 1, 2),
    (408, NULL, 'Refusée', 'STATUT_FORMATION', 3, 0, 0, 1, 3),
    (409, NULL, 'Annulée', 'STATUT_FORMATION', 3, 0, 0, 1, 4),
    (410, NULL, 'En attente', 'STATUT_PARTICIPATION', 3, 0, 0, 1, 1),
    (411, NULL, 'A participé', 'STATUT_PARTICIPATION', 3, 0, 0, 1, 2),
    (412, NULL, 'N\'a pas participé', 'STATUT_PARTICIPATION', 3, 0, 0, 1, 3),
    (413, NULL, 'En attente', 'STATUT_EVAL_FORMATION', 3, 0, 0, 1, 1),
    (414, NULL, 'Evaluée', 'STATUT_EVAL_FORMATION', 3, 0, 0, 1, 2);