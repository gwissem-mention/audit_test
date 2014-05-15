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

/* Nettoyage de cotrine migration */
/* 16:11:15 Gaia (localnodevo) */ DROP TABLE `migration_versions`;

/* -------------------*/
/* ----   Front   ----*/
/* -------------------*/
/* Gestion des habilitations du front : GME - 29/04/14 */
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (30, 'FrontOffice - Gestion des Modules', '/^\\/module/', 30, 2);

/* --- Fomulaire d'évaluation : GME - 05/05/14 --- */
INSERT INTO `hn_questionnaire_questionnaire` (`qst_id`, `qst_nom`, `qst_lock`)
VALUES
    (4, 'Module - Evaluation', 1);
INSERT INTO `hn_questionnaire_type_question` (`typ_id`, `libelle`, `nom`)
VALUES
    (8, 'radio', 'Radio');

INSERT INTO `hn_questionnaire_question` (`que_id`, `qst_id`, `typ_question`, `que_libelle`, `que_obligatoire`, `que_verifJS`, `que_ordre`, `que_alias`, `que_reference_param_tri`)
VALUES
    (39, 4, 8, 'Ces objectifs énoncés en début de session ont-ils-été atteints ?', 0, 'etoiles', 1, 'module_objectif_enonces_atteints', NULL),
    (40, 4, 8, 'Les apports de cette session sont-ils applicables dans le cadre de vos missions ?', 0, 'etoiles', 2, 'module_apports_session_applicables', NULL),
    (41, 4, 8, 'Globalement estimez-vous utile d\'avoir suivi cette formation ?', 1, 'oui-non validate[required]', 3, 'module_formation_utilie', NULL),
    (42, 4, 8, 'Le temps imparti pour cette formation était il suffisant ?', 0, 'etoiles', 4, 'module_temps_suffisant', NULL),
    (43, 4, 8, 'Les questions abordées correspondaient-elles à vos attentes ?', 0, 'etoiles', 5, 'module_questions_correspondaient_attente', NULL),
    (44, 4, 8, 'Glablement êtes-vous satisfait du contenu du module ?', 1, 'etoiles validate[required]', 6, 'module_statisfait_module', NULL),
    (45, 4, 8, 'Y a-t-il eu suffisamment d\'occasions pour les participants d\'être actifs ?', 0, 'etoiles', 7, 'module_occasion_participant_actif', NULL),
    (46, 4, 8, 'Quelle est votre appréciation des compétences pédagogiques de l\'intervenant ?', 0, 'etoiles', 8, 'module_competance_pedagogique_intervenant', NULL),
    (47, 4, 8, 'Globalement êtes-vous satisfait des modalités pédagogique proposées ?', 1, 'oui-non validate[required]', 9, 'module_satisfait_modalites_pedagogique', NULL),
    (48, 4, 8, 'Quelle est votre appréciation des informations fournies avant la formation ?', 0, 'etoiles', 10, 'module_appreciation_informations_fournies', NULL),
    (49, 4, 8, 'Les conditions matérielles étaient elles adaptées à cette formation ?', 0, 'etoiles', 11, 'module_conditions_materielles_adaptees', NULL),
    (50, 4, 8, 'Globalement êtes-vous satisfait des modalités pratiques de cette formation ?', 1, 'oui-non validate[required]', 12, 'module_satisfait_modile_pratique', NULL),
    (51, 4, 2, 'Avez-vous des suggestions d\'amélioration ou des remarques dont vous souhaiteriez nous faire part ?', 0, NULL, 13, 'module_suggestion_faire_part', NULL),
    (52, 4, 2, 'Dans le cadre de vos projets dans ce domaine, rencontrez-vous des problématiques qui ne trouvent pas de réponse dans le cadre de la session suvie ?', 0, NULL, 14, 'module_problematique_sans_reponse', NULL);

/* Lien de menu front : 12/05/2014 */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (137, NULL, 3, 'Modules thématiques', 'hopitalnumerique_module_module_front', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7);

/* Mail du form d'évaluation : GME - 12/05/2014 */
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES
    (33, '[HOPITALNUMERIQUE] - Evaluation d\'une session', 'Formulaire de l\'évaluation à une session d\'un module', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\r\nVôtre participation à la session %module du %date a été notifié.\r\nVous pouvez accèder au formulaire d\'évaluation de la session ici : %url.\r\n\r\nCordialement,', '{\"%u\":\"Nom d\'utilisateur\",\"%module\":\"Nom du module\",\"%date\":\"Date de la session\",\"%url\":\"Lien du formulaire d\'évaluation.\"}');

/* Lien de menu front : GME - 13/05/2014 */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (138, 137, 3, 'Modules thématiques - Affichage', 'hopitalnumerique_module_module_show_front', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
    (139, 137, 3, 'Modules thématiques - Session - Information', 'hopitalnumerique_module_session_informations_front', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2),
    (140, 137, 3, 'Modules thématiques - Session - Evaluation', 'hopitalnumerique_module_evaluation_form_front', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3);

/* Nettoyage des références evaluation : GME - 13/05/2014 */
/* 14:17:44 Gaia (localnodevo) */ DELETE FROM `hn_reference` WHERE `ref_id` IN ('413','414');

/* Ajout lien de menu compte-hn */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (141, NULL, 4, 'Modules thématiques', 'hopitalnumerique_module_inscription_index_front', NULL, NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 11);

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (31, 'FrontOffice - Tableau de bord : Modules thématiques', '/^\\/compte-hn\\/module-thematiques/', 31, 2);

/* Mail */
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES
    (34, '[HOPITALNUMERIQUE] - Inscription à une session', 'Inscription d\'un utilisateur à une session', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\r\nVotre inscription à la session %module du %date a été prise en compte.\r\n\r\nCordialement,', '{\"%u\":\"Nom d\'utilisateur\",\"%module\":\"Nom du module\",\"%date\":\"Date de la session\"}');
