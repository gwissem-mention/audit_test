/* 14:08:20 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Liste déroulante' WHERE `typ_id` = '5';
/* 14:09:38 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Liste déroulante à choix multiples' WHERE `typ_id` = '10';
/* 14:10:40 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Bouttons radios' WHERE `typ_id` = '8';
/* 14:12:07 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Case à cocher' WHERE `typ_id` = '4';
/* 14:12:56 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Ajout de fichier' WHERE `typ_id` = '3';
/* 15:27:37 Gaia (localnodevo) */ INSERT INTO `hn_questionnaire_type_question` (`typ_id`, `libelle`, `nom`) VALUES (NULL, 'entitycheckbox', 'Cases à cocher à choix multiples');

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (181, NULL, 1, 'Gestion des questionnaires', 'hopitalnumerique_questionnaire_index', '[]', NULL, NULL, 'fa fa-check-square-o', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 181, 1, 'Edition questionnaire', 'hopitalnumerique_questionnaire_edit_questionnaire', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 0);
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 181, 1, 'Edition questionnaire', 'hopitalnumerique_questionnaire_add_questionnaire', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 0);
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 181, 1, 'Edition questionnaire', 'hopitalnumerique_questionnaire_question_index', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 0);
