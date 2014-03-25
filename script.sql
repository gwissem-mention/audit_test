/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* GME - 13/02/2014
   PROD -> DEV
   Lorem ipsum 

INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`) VALUES
(26, '[HOPITALNUMERIQUE] - Mot de passe perdu', 'Mail de réinitialisation du mot de passe', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\n\nVotre mot de passe a été réinitialisé, merci de cliquer sur le lien suivant pour continuer : %url \n\nCordialement,');*/


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

UPDATE `hn_questionnaire_question` SET `que_obligatoire` = '0' WHERE `hn_questionnaire_question`.`que_id` =25;

---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Update objets -> publication 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Gestion des publications' WHERE `core_menu_item`.`itm_id` = 46; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Ajouter une publication' WHERE `core_menu_item`.`itm_id` = 47; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Editer une publication' WHERE `core_menu_item`.`itm_id` = 48; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Fiche publication' WHERE `core_menu_item`.`itm_id` = 49; 
UPDATE `wwwhopitalnumeriquecom`.`core_menu_item` SET `itm_name` = 'Recherche - vue publication' WHERE `core_menu_item`.`itm_id` = 79;*/

