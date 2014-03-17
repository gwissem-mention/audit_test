/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */
INSERT .....

/* GME - 19/02/14
   DEv -> PROD
   Ajout des amb/expert + prod maitrisé dans le menu
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(57, 9, 1, 'Formulaire ambassadeur', 'hopitalnumerique_user_ambassadeur_edit', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2),
(58, 9, 1, 'Formulaire expert', 'hopitalnumerique_user_expert_edit', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(59, 9, 1, 'Production maitrisée', 'hopitalnumerique_user_ambassadeur_objets', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 0);*/

/* GME - 20/02/14
   DEV -> PROD
   Ajout question DPI questionnaire expert*/
/*INSERT INTO `hn_questionnaire_question` (`que_id`, `qst_id`, `typ_question`, `que_libelle`, `que_obligatoire`, `que_verifJS`, `que_ordre`, `que_alias`, `que_reference_param_tri`) VALUES
(NULL, 1, 3, 'Joindre votre DPI', 1, NULL, 16, 'dpi', NULL);*/

/* QSO - 25/02/14
   DEV -> PROD
   Ajout lien de menu*/
/*INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(55, 16, 1, 'Etablissements  non référencés', 'hopitalnumerique_etablissement_autres', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4);*/

/* RLE - 25/02/2014
   DEV -> PROD
   Modif URL gestion des médias */
/*UPDATE `core_menu_item` SET `itm_route` = 'nodevo_gestionnaire_media_index' WHERE `core_menu_item`.`itm_id` =44;*/

/* GME - 07/03/2014
   DEV -> PROD
   Ajout lien de menu
INSERT INTO `wwwhopitalnumeriquecom`.`core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, NULL, '4', 'Candidature pour devenir ambassadeur', 'hopitalnumerique_user_ambassadeur_front_edit', NULL, NULL, NULL, NULL, '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '8');
INSERT INTO `wwwhopitalnumeriquecom`.`core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, NULL, '4', 'Candidature pour devenir expert', 'hopitalnumerique_user_expert_front_edit', NULL, NULL, NULL, NULL, '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '9');*/

/* GME - 10/03/2014
   DEV -> PROD
   Modifs questionnaire
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '6' WHERE `hn_questionnaire_question`.`que_id` =16;
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '3' WHERE `hn_questionnaire_question`.`que_id` =17;
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '4' WHERE `hn_questionnaire_question`.`que_id` =18;
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '5' WHERE `hn_questionnaire_question`.`que_id` =19;*/

/* GME - 10/03/2014
   DEV -> PROD
   Modifs items menu
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_route` = 'hopital_numerique_user_informations_personnelles', `itm_route_absolute` = NULL, `itm_uri` = NULL WHERE `core_menu_item`.`itm_id` = 69;*/

/* QSO - 11/03/2014
   DEV -> PROD
   Modifs items menu
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_route` = 'hopital_numerique_requete_homepage', `itm_uri` = NULL WHERE `core_menu_item`.`itm_id` = 70;*/


/* GME - 11/03/2014
   DEV -> PROD
   Mails 
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`) VALUES
(2, '[HOPITALNUMERIQUE] - Inscription à Hôpital Numérique', 'Inscription en Front Office', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\r\nVoici votre mot de passe : %p \r\n\r\nVous pouvez vous connecter sur le site %s en entrant votre adresse mail et votre mot de passe.\r\n\r\nCordialement,'),
(3, '[HOPITALNUMERIQUE] - Création d''une demande d''intervention', 'Création d''une demande d''intervention', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Votre demande d''intervention a correctement été créée. Cordialement,'),
(4, '[HOPITALNUMERIQUE] - Demande d''intervention', 'Acceptation ou non d''une demande d''intervention par l''ambassadeur', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''intervention a été créée. Vous puvez la valider ou la refuser en visitant : %l Cordialement,'),
(5, '[HOPITALNUMERIQUE] - Demande d''intervention', 'Alerte référent d''une demande d''intervention émise par un CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été émise en votre nom et va être étudiée par l''ambassadeur. Cordialement,'),
(6, '[HOPITALNUMERIQUE] - Demande d''intervention acceptée par le CMSI', 'Demande d''intervention acceptée par le CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été acceptée. Vous pouvez vous rendre à votre interface pour la gérer. Cordialement,'),
(7, '[HOPITALNUMERIQUE] - Demande d''intervention refusée par le CMSI', 'Demande d''intervention refusée par le CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été refusée. %c Cordialement,');*/

/*QSO - 12/03/2014
   DEV -> PROD
   Modifs items menu */
/*UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_uri` = 'javascript:alert(''Forum Hôpital Numérique bientôt disponible.'');' WHERE `core_menu_item`.`itm_id` = 67;
INSERT INTO `wwwhopitalnumeriquecom`.`core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`) VALUES (NULL, 'Système de recherche FrontOffice', '/^\\/recherche-par-referencement/', '12');
INSERT INTO `wwwhopitalnumeriquecom`.`core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`) VALUES (NULL, 'Accès aux publications FrontOffice', '/^\\/publication/', '13');
INSERT INTO `wwwhopitalnumeriquecom`.`core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`) VALUES (NULL, 'Accès aux requetes FrontOffice', '/^\\/requetes/', '14');
INSERT INTO `wwwhopitalnumeriquecom`.`core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '66', '3', 'Recherche - Requete appliquée', 'hopital_numerique_recherche_homepage_requete', NULL, '0', NULL, NULL, '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');*/

/*QSO - 13/03/2014
   DEV -> PROD
   Modifs items menu 
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '9', '1', 'Domaines fonctionnels maitrisés', 'hopitalnumerique_user_ambassadeur_domainesFonctionnels', NULL, '0', NULL, NULL, '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '10');

/* RLE - 14/03/2014
   DEV -> PROD
   Interventions
INSERT INTO `hn_intervention_initiateur` (
`intervinit_id` ,
`intervinit_type`
)
VALUES (
1 , 'CMSI'
), (
2 , 'Établissement'
);

ALTER TABLE `hn_intervention_demande` CHANGE `directeur_id` `directeur_id` INT( 11 ) NULL COMMENT 'Le directeur de l''ES concerné';

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'10', '[HOPITALNUMERIQUE] - Demande d''intervention', 'Acceptation ou non d''une demande d''intervention par le CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''intervention a été créée. Vous puvez la valider ou la refuser en visitant : %l Cordialement,'
);

UPDATE `core_mail` SET `mail_body` = 'Bonjour %u, Une demande d''''intervention a été refusée. %l Cordialement,' WHERE `core_mail`.`mail_id` =7;

ALTER TABLE `hn_intervention_demande` ADD `interv_cmsi_date_derniere_relance` DATETIME NULL DEFAULT NULL COMMENT 'Date de la dernière relance envoyée au CMSI.' AFTER `interv_ambassadeur_date_choix` ;

INSERT INTO `hn_intervention_regroupement_type` (
`intervregtyp_id` ,
`intervregtyp_libelle`
)
VALUES (
'1', 'Objet similaire'
), (
'2', 'Ambassadeur'
);

ALTER TABLE `hn_intervention_demande` ADD INDEX ( `interv_date_creation` ) ;

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'15', '[HOPITALNUMERIQUE] - Invitation référent pour évaluation', 'Invitation référent pour évaluation', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Vous pouvez dès à présent évaluer cette intervention : %l Cordialement,'
);

ALTER TABLE `hn_intervention_evaluation` ADD UNIQUE (
`interv_id`
);

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'16', '[HOPITALNUMERIQUE] - Intervention // Changement d''ambassadeur', 'Intervention // Changement d''ambassadeur', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Nouvel ambassadeur = %a : %l Cordialement,'
);

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'17', '[HOPITALNUMERIQUE] - Demande d''intervention acceptée par l''ambassadeur', 'Demande d''intervention acceptée par l''ambassadeur', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été acceptée. Vous pouvez vous rendre à votre interface pour la visualiser : %l Cordialement,'
);

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'18', '[HOPITALNUMERIQUE] - Demande d''intervention refusée par l''ambassadeur', 'Demande d''intervention refusée par l''ambassadeur', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été refusée. Vous pouvez vous rendre à votre interface pour la visualiser : %l Cordialement,'
i);*/

/*QSO - 14/03/2014
   DEV -> PROD
   Add ressource + lien menu 
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`) VALUES (NULL, 'FrontOffice - Accès à la solocitation des ambassadeurs', '/^\\/registre-ambassadeurs/', '15');
UPDATE `core_menu_item` SET `mnu_menu` = '3', `itm_order` = '5', `itm_route` = 'hopital_numerique_registre_homepage', `itm_uri` = NULL WHERE `core_menu_item`.`itm_id` = 75;*/
*/

/* RLE - 14/03/2014
   DEV -> PROD
   Interventions
INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'19', '[HOPITALNUMERIQUE] - Demande d''intervention // Relance ambassadeur 1', 'Demande d''intervention // Relance ambassadeur 1', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention est en attente : %l Cordialement,'
);

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'20', '[HOPITALNUMERIQUE] - Demande d''intervention // Relance ambassadeur 2', 'Demande d''intervention // Relance ambassadeur 2', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention est encore en attente : %l Cordialement,'
);

ALTER TABLE `hn_intervention_demande` ADD `interv_ambassadeur_date_derniere_relance` DATETIME NULL DEFAULT NULL COMMENT 'Date de la dernière relance envoyée à l''ambassadeur.' AFTER `interv_cmsi_date_derniere_relance` ;


UPDATE `core_menu_item` SET `itm_route` = 'hopital_numerique_intervention_demande_liste',
`itm_uri` = NULL WHERE `core_menu_item`.`itm_id` =73;

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'21', '[HOPITALNUMERIQUE] - Demande d''intervention // Relance ambassadeur avant cloture', 'Demande d''intervention // Relance ambassadeur avant cloture', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention est encore en attente : %l Cordialement,'
);

UPDATE `core_mail` SET `mail_objet` = '[HOPITALNUMERIQUE] - Demande d''intervention // Relance ambassadeur / Cloture',
`mail_description` = 'Demande d''intervention // Relance ambassadeur / Cloture' WHERE `core_mail`.`mail_id` =21;

INSERT INTO `hn_questionnaire_questionnaire` (
`qst_id` ,
`qst_nom` ,
`qst_lock`
)
VALUES (
NULL , 'Évaluation', '1'
);

INSERT INTO `hn_questionnaire_question` (`que_id`, `qst_id`, `typ_question`, `que_libelle`, `que_obligatoire`, `que_verifJS`, `que_ordre`, `que_alias`, `que_reference_param_tri`) VALUES (NULL, '3', '5', 'Les prod ANAP etc', '0', NULL, '1', 'evaluation_prod_anap', 'NOTE_EVALUATION');*/

/* RLE - 17/03/2014
   DEV -> PROD
   Interventions
 
INSERT INTO `hn_questionnaire_type_question` (
`typ_id` ,
`libelle` ,
`nom`
)
VALUES (
'6', 'date', 'Date'
);

ALTER TABLE `hn_questionnaire_reponse` ADD `param_id` INT UNSIGNED NULL COMMENT 'Éventuelle clef étrangère.',
ADD INDEX ( `param_id` ) ;


INSERT INTO `hn_reference` (
`ref_id` ,
`parent_id` ,
`ref_libelle` ,
`ref_code` ,
`ref_etat` ,
`ref_dictionnaire` ,
`ref_recherche` ,
`ref_lock` ,
`ref_order`
)
VALUES (
NULL , NULL , 'Bien', 'NOTE_EVALUATION', '3', '0', '0', '1', '1'
), (
NULL , NULL , 'Mal', 'NOTE_EVALUATION', '3', '0', '0', '1', '1'
);


INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'22', '[HOPITALNUMERIQUE] - Demande d''intervention // Évaluation', 'Demande d''intervention // Évaluation', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une évaluation a été créée : %l Cordialement,'
);

INSERT INTO `hn_questionnaire_type_question` (
`typ_id` ,
`libelle` ,
`nom`
)
VALUES (
'7', 'interventionobjets', 'Objets de l''intervention'
);

/* RLE - 17/03/2014
   DEV -> PROD
   Interventions

UPDATE `core_ressource` SET `res_nom` = 'FrontOffice - Accès à la sollicitation des ambassadeurs',
`res_pattern` = '/^\\/(registre-ambassadeurs|intervention)/' WHERE `core_ressource`.`res_id` =15;*/
`res_pattern` = '/^\\/(registre-ambassadeurs|intervention)/' WHERE `core_ressource`.`res_id` =15;






/* RLE - 17/03/2014
   DEV -> PROD
   Interventions*/

UPDATE `core_ressource` SET `res_pattern` = '/^\\/(registre-ambassadeurs|compte-hn\\/intervention)/' WHERE `core_ressource`.`res_id` =15;

INSERT INTO `core_ressource` (
`res_id` ,
`res_nom` ,
`res_pattern` ,
`res_order` ,
`res_type`
)
VALUES (
'20', 'FrontOffice - Gestion des intervention', '/^\\/compte-hn\\/intervention/demandes/liste', '20', '2'
);
