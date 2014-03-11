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
   Modifs items menu*/
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_route` = 'hopital_numerique_user_inscription', `itm_route_absolute` = NULL, `itm_uri` = NULL WHERE `core_menu_item`.`itm_id` = 69;
