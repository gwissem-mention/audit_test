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


/* 22/05 %/
/* 15:34:16 HN */ UPDATE `core_menu` SET `mnu_alias` = 'menu-footer-front_gen' WHERE `mnu_id` = '2';

/* 25/05  Lien de menu front pour parcours guidé */
/* 10:38:13 HN */ UPDATE `core_menu_item` SET `itm_route_parameters` = '{\"id\":\"1\"}' WHERE `itm_id` = '194';


/* 26/05 : Menu mon compte */
INSERT INTO `core_menu` (`mnu_id`, `mnu_name`, `mnu_alias`, `mnu_cssClass`, `mnu_cssId`, `mnu_lock`)
VALUES
    (4, 'Menu Principal Front générique', 'menu-main-front_gen', 'menu-main', 'menu', 1);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, NULL, 4, 'Informations personnelles', 'hopital_numerique_user_informations_personnelles', NULL, NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
    (NULL, NULL, 4, 'Requêtes de recherche', 'hopital_numerique_requete_homepage', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4),
    (NULL, NULL, 4, 'Mes autodiagnostics', 'hopitalnumerique_autodiag_front_comptehn', '[]', 0, '#', NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 5),
    (NULL, NULL, 4, 'Changer mon mot de passe', 'hopital_numerique_user_motdepasse', '[]', NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2);

INSERT INTO `hn_domaine` (`dom_id`, `temp_id`, `dom_nom`, `dom_description`, `dom_url`, `dom_logo`, `dom_date_derniere_maj`, `dom_adresse_mail_contact`)
VALUES
	(1, 2, 'Mon Hôpital Numérique', 'Faciliter l\'usage du numérique au service des soins', 'http://test.hopitalnumeriquemulti.nodevo.com', 'logo-hn.jpg', '2015-05-27 00:00:00', 'accompagnement-hn@anap.fr'),
	(2, 1, 'Macrodiagnostic', 'Macrodiagnostic - Mesurer le niveau de maturité de mon établissement afin d’identifier des actions d’améliorations.', 'http://macrodiag.hopitalnumeriquemulti.nodevo.com', NULL, '2015-05-27 00:00:00', 'accompagnement-hn@anap.fr');

/* Recherche aidée */
INSERT INTO `hn_recherche_expbesoin_gestionnaire` (`expbg_id`, `expbg_nom`)
VALUES
	(1, 'Recherche Aidée HNum');
UPDATE hn_recherche_expbesoin SET expbg_id = 1;

/*Parcours guidé*/
INSERT INTO `hn_recherche_recherche_parcours_gestion` (`rrpg_id`, `rrpg_nom`)
VALUES
	(1, 'Parcours guidé HNum');
INSERT INTO `hn_recherche_recherche_parcours_gestion_reference_parente` (`rrpg_id`, `ref_id`)
VALUES
	(1, 291),
	(1, 292),
	(1, 293),
	(1, 294),
	(1, 295);
INSERT INTO `hn_recherche_recherche_parcours_gestion_reference_ventilation` (`rrpg_id`, `ref_id`)
VALUES
	(1, 226),
	(1, 233),
	(1, 234),
	(1, 235),
	(1, 236),
	(1, 237);

INSERT INTO `hn_domaine_gestions_parcours_guide` (`rrpg_id`, `dom_id`)
VALUES
	(1, 1);
UPDATE hn_recherche_recherche_parcours SET rrpg_id = 1
INSERT INTO `hn_recherche_recherche_parcours_gestion_type_publication` (`rrpg_id`, `ref_id`)
VALUES
	(1, 184);

	
