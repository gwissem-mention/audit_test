/* #3951 */
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES ('60', '[%subjectDomaine] - Activités des experts - Contrat', 'Activités des experts - Contrat', '%mailContactDomaineCurrent', 'ANAP - %nomContactDomaineCurrent', 'Bonjour, Voici le contrat en PJ. Cordialement,', '');
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES ('61', '[%subjectDomaine] - Activités des experts - Paiement', 'Activités des experts - Paiement', '%mailContactDomaineCurrent', 'ANAP - %nomContactDomaineCurrent', 'Bonjour, Voici le paiement en PJ. Cordialement,', '');

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES (NULL, '220', '1', 'Paiement', 'hopitalnumerique_expert_expert_paiement', NULL, '0', NULL, NULL, '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '1');

/* Nouvelles régions : Application des nouveaux forfaits */
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES ('62', '[%subjectDomaine] - Demande d''intervention - Sollicitation sans établissement', 'Demande d''intervention - Sollicitation sans établissement', '%mailContactDomaineCurrent', 'ANAP - %nomContactDomaineCurrent', 'Bonjour, Un utilisateur a effectué une sollicitation d''ambassadeur sans renseigner son établissement.', '');
