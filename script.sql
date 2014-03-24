/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* GME - 13/02/2014
   PROD -> DEV
   Lorem ipsum */

/*UPDATE `core_mail` SET `mail_body` = 'Bonjour %u, %candidat vient de soumettre une candidature ambassadeur. Voici ses informations : %questionnaire Cordialement,' WHERE `core_mail`.`mail_id` = 24;

/* Modiciation de l’ordre des items suivants : */
/*UPDATE `core_menu_item` SET `itm_order` = '3' WHERE `core_menu_item`.`itm_id` = 70;
UPDATE `core_menu_item` SET `itm_order` = '4' WHERE `core_menu_item`.`itm_id` = 71; 
UPDATE `core_menu_item` SET `itm_order` = '5' WHERE `core_menu_item`.`itm_id` = 72;
UPDATE `core_menu_item` SET `itm_order` = '6' WHERE `core_menu_item`.`itm_id` = 73;
UPDATE `core_menu_item` SET `itm_order` = '7' WHERE `core_menu_item`.`itm_id` = 74;*/

/* Insertion en 2nd place du ‘modifier mon mot de passe’ 
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(NULL, NULL, 4, 'Modication mot de passe', 'hopital_numerique_user_motdepasse', NULL, NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2);

/* A vérfiier avant si tu ne les as pas déjà exécutés : 
DELETE FROM `core_mail` WHERE `core_mail`.`mail_id` = 23;

INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`) VALUES
(24, '[HOPITALNUMERIQUE] - Candidature ambassadeur', 'Nouvelle candidature ambassadeur / Envoie CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\n\n %candidat vient de soumettre une candidature ambassadeur. \n\nVoici ses informations : \n%candidature \n\nCordialement,');

UPDATE `core_ressource` SET `res_pattern` = '/^\\/compte-hn\\/(informations-personnelles|mot-de-passe)/' WHERE `core_ressource`.`res_id` = 18;*/


---------------------------------------------------------------------------------------------
/* RLE - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* QSO - 13/02/2014
   PROD -> DEV
   Lorem ipsum */

