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
/*UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_uri` = 'javascript:alert(''Forum Hôpital Numérique bientôt disponible.'');' WHERE `core_menu_item`.`itm_id` = 67;*/
INSERT INTO `wwwhopitalnumeriquecom`.`core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`) VALUES (NULL, 'Système de recherche FrontOffice', '/^\\/recherche-par-referencement/', '12');
INSERT INTO `wwwhopitalnumeriquecom`.`core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`) VALUES (NULL, 'Accès aux publications FrontOffice', '/^\\/publication/', '13');
INSERT INTO `wwwhopitalnumeriquecom`.`core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`) VALUES (NULL, 'Accès aux requetes FrontOffice', '/^\\/requetes/', '14');
INSERT INTO `wwwhopitalnumeriquecom`.`core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, '66', '3', 'Recherche - Requete appliquée', 'hopital_numerique_recherche_homepage_requete', NULL, '0', NULL, NULL, '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '3');