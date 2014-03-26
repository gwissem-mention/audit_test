/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* GME - 13/02/2014
   PROD -> DEV
   Lorem ipsum */

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(NULL, 68, 3, 'Informations personnelles', 'hopital_numerique_user_informations_personnelles', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 68, 3, 'Requêtes de recherche', 'hopital_numerique_requete_homepage', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3),
(NULL, 68, 3, 'Outils d''autodiagnostic', NULL, NULL, 0, '#', NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4),
(NULL, 68, 3, 'Les formations', NULL, NULL, 0, '#', NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 5),
(NULL, 68, 3, 'Les interventions', NULL, NULL, 0, 'hopital_numerique_intervention_demande_liste', NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 6),
(NULL, 68, 3, 'Echanger avec l''ANAP', NULL, NULL, 0, '#', NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
(NULL, 68, 3, 'Candidature pour devenir ambassadeur', 'hopitalnumerique_user_ambassadeur_front_edit', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 8),
(NULL, 68, 3, 'Candidature pour devenir expert', 'hopitalnumerique_user_expert_front_edit', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 9),
(NULL, 68, 3, 'Modication mot de passe', 'hopital_numerique_user_motdepasse', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2);


---------------------------------------------------------------------------------------------
/* RLE - 13/02/2014
   PROD -> DEV
   Lorem ipsum 

INSERT INTO `core_menu_item` (
`itm_id` ,
`itm_parent` ,
`mnu_menu` ,
`itm_name` ,
`itm_route` ,
`itm_route_parameters` ,
`itm_route_absolute` ,
`itm_uri` ,
`itm_icon` ,
`itm_display` ,
`itm_display_children` ,
`itm_role` ,
`itm_order`
)
VALUES (
NULL , '73', '4', 'Lire une demande d''intervention', 'hopital_numerique_intervention_demande_voir', NULL , '0', NULL , NULL , '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '5'
);
INSERT INTO `core_menu_item` (
`itm_id` ,
`itm_parent` ,
`mnu_menu` ,
`itm_name` ,
`itm_route` ,
`itm_route_parameters` ,
`itm_route_absolute` ,
`itm_uri` ,
`itm_icon` ,
`itm_display` ,
`itm_display_children` ,
`itm_role` ,
`itm_order`
)
VALUES (
NULL , '73', '4', 'Suivi des demandes d''intervention', 'hopital_numerique_intervention_demande_suivi_demandes', NULL , '0', NULL , NULL , '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '5'
);
INSERT INTO `core_menu_item` (
`itm_id` ,
`itm_parent` ,
`mnu_menu` ,
`itm_name` ,
`itm_route` ,
`itm_route_parameters` ,
`itm_route_absolute` ,
`itm_uri` ,
`itm_icon` ,
`itm_display` ,
`itm_display_children` ,
`itm_role` ,
`itm_order`
)
VALUES (
NULL , '73', '4', 'Nouvelle demande d''intervention', 'hopital_numerique_intervention_demande_nouveau', NULL , '0', NULL , NULL , '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '5'
);
INSERT INTO `core_menu_item` (
`itm_id` ,
`itm_parent` ,
`mnu_menu` ,
`itm_name` ,
`itm_route` ,
`itm_route_parameters` ,
`itm_route_absolute` ,
`itm_uri` ,
`itm_icon` ,
`itm_display` ,
`itm_display_children` ,
`itm_role` ,
`itm_order`
)
VALUES (
NULL , '73', '4', 'Nouvelle évaluation d''une demande d''intervention', 'hopital_numerique_intervention_evaluation_nouveau', NULL , '0', NULL , NULL , '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '5'
);
INSERT INTO `core_menu_item` (
`itm_id` ,
`itm_parent` ,
`mnu_menu` ,
`itm_name` ,
`itm_route` ,
`itm_route_parameters` ,
`itm_route_absolute` ,
`itm_uri` ,
`itm_icon` ,
`itm_display` ,
`itm_display_children` ,
`itm_role` ,
`itm_order`
)
VALUES (
NULL , '73', '4', 'Édition d''une demande d''intervention', 'hopital_numerique_intervention_demande_edit', NULL , '0', NULL , NULL , '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '5'
);
INSERT INTO `core_menu_item` (
`itm_id` ,
`itm_parent` ,
`mnu_menu` ,
`itm_name` ,
`itm_route` ,
`itm_route_parameters` ,
`itm_route_absolute` ,
`itm_uri` ,
`itm_icon` ,
`itm_display` ,
`itm_display_children` ,
`itm_role` ,
`itm_order`
)
VALUES (
NULL , '73', '4', 'Lire une évaluation d''une demande d''intervention', 'hopital_numerique_intervention_evaluation_voir', NULL , '0', NULL , NULL , '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '5'
);*/

/*UPDATE `hn_questionnaire_question` SET `que_obligatoire` = '0' WHERE `hn_questionnaire_question`.`que_id` =25;*/

/*UPDATE `core_menu_item` SET `itm_route` = 'hopital_numerique_intervention_demande_liste' WHERE `core_menu_item`.`itm_id` =73;
UPDATE `core_menu_item` SET `itm_uri` = NULL WHERE `core_menu_item`.`itm_id` =73;

UPDATE `core_menu_item` SET `itm_route` = 'hopital_numerique_intervention_demande_liste' WHERE `core_menu_item`.`itm_id` =91;
UPDATE `core_menu_item` SET `itm_uri` = NULL WHERE `core_menu_item`.`itm_id` =91;

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(NULL, 73, 4, 'Lire une demande d''intervention', 'hopital_numerique_intervention_demande_voir', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 73, 4, 'Suivi des demandes d''intervention', 'hopital_numerique_intervention_demande_suivi_demandes', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 73, 4, 'Nouvelle demande d''intervention', 'hopital_numerique_intervention_demande_nouveau', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 73, 4, 'Nouvelle évaluation d''un demande d''intervention', 'hopital_numerique_intervention_evaluation_nouveau', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 73, 4, 'Édition d''une demande d''intervention', 'hopital_numerique_intervention_demande_edit', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 73, 4, 'Lire une évaluation d''une demande d''intervention', 'hopital_numerique_intervention_evaluation_voir', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(NULL, 68, 3, 'Lire une demande d''intervention', 'hopital_numerique_intervention_demande_voir', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 68, 3, 'Suivi des demandes d''intervention', 'hopital_numerique_intervention_demande_suivi_demandes', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 68, 3, 'Nouvelle demande d''intervention', 'hopital_numerique_intervention_demande_nouveau', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 68, 3, 'Nouvelle évaluation d''un demande d''intervention', 'hopital_numerique_intervention_evaluation_nouveau', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 68, 3, 'Édition d''une demande d''intervention', 'hopital_numerique_intervention_demande_edit', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(NULL, 68, 3, 'Lire une évaluation d''une demande d''intervention', 'hopital_numerique_intervention_evaluation_voir', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);*/


---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Update objets -> publication 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Gestion des publications' WHERE `core_menu_item`.`itm_id` = 46; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Ajouter une publication' WHERE `core_menu_item`.`itm_id` = 47; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Editer une publication' WHERE `core_menu_item`.`itm_id` = 48; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Fiche publication' WHERE `core_menu_item`.`itm_id` = 49; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Recherche - vue publication' WHERE `core_menu_item`.`itm_id` = 79;*/

