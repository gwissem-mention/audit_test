/* ACL pour les domaines */
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
	(NULL, 'BackOffice - Gestion des domaines', '/^\\/admin\\/domaine/', 15, 1);
/* Menu Back pour les domaines */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(210, 16, 1, 'Domaines', 'hopitalnumerique_domaine_admin_domaine', '[]', 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10),
	(NULL, 210, 1, 'Ajouter un domaine', 'hopitalnumerique_domaine_admin_domaine_add', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
	(NULL, 210, 1, 'Editer domaine', 'hopitalnumerique_domaine_admin_domaine_edit', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);
/* Ajout du mail d'alerte de modif */
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES
	(41, 'Modification d’inscription par un gestionnaire', 'Modification d’inscription par un gestionnaire', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\nVous venez d\'être associé au(x) domaine(s) suivant : %domaines\r\n\nCordialement,', '{\"%u\":\"Nom d\'utilisateur\",\"%domaines\":\"Domaines de l\'utilisateur\"}');

INSERT INTO `hn_domaine_template` (`temp_id`, `temp_nom`)
VALUES
	(1, 'Template générique'),
	(2, 'Template HNum');


/* A voir si tu as les mêmes items de menu que moi, mais il ne faut as oublié de les modifier ! */
/* 19/05:  */
/* 15:44:49 HN */ UPDATE `core_menu_item` SET `itm_name` = 'Parcours guidé', `itm_route` = 'hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion' WHERE `itm_id` = '192';

/* 20/05 */
/* 09:59:45 HN */ UPDATE `core_menu_item` SET `itm_route` = 'hopitalnumerique_recherche_admin_aide-expression-besoin_gestion' WHERE `itm_id` = '191';
