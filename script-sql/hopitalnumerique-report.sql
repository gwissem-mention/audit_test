INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, NULL, 1, 'Gestion des signalements de bug', 'hopitalnumerique_report_admin_report', '[]', NULL, NULL, 'fa glyphicon-arrow-up', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 8);

INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES
    (40, '[HOPITALNUMERIQUE] - Signalement de bug', 'Signalement de bug via le formulaire', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\nL\'utilisateur %rapporteur a signalé une anomalie le %date sur la page %url . \r\nVoici son userAgent : %agentUser \r\nEt ses observations : %observations\r\n\nCordialement,', '{\"%u\":\"Nom d\'utilisateur\",\"%rapporteur\":\"Nom de la personne ayant relevé le bug\",\"%date\":\"Date du signalement\",\"%agentUser\":\"Information sur le navigateur et la machine de l\'utilisateur\",\"%observations\":\"Observations du rapporteur\"}');
