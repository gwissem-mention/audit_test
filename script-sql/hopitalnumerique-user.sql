/* 10:10:29 Gaia (localnodevo) */ DELETE FROM `core_mail` WHERE `mail_id` IN ('24');


INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES
    (24, '[HOPITALNUMERIQUE] - Candidature ambassadeur pour l\'ARS', 'Nouvelle candidature ambassadeur / Envoi CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\nL\'utilisateur suivant vient de soumettre une candidature ambassadeur : %candidat\r\n\r\nVoici ses informations : %questionnaire \r\n\r\nCordialement,', '{\"%u\":\"Nom d\'utilisateur\",\"%candidat\":\"Nom du candidat\",\"%questionnaire\":\"Questionnaire\"}');
