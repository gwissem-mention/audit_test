/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* GME - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* RLE - 28/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Update objets -> publication */
INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`) VALUES
(28, '[HOPITALNUMERIQUE] - Candidature expert', 'Nouvelle candidature expert / Envoie Admins', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\r\n%candidat vient de soumettre une candidature expert. \r\n\r\nVoici ses informations : \r\n%questionnaire \r\n\r\nCordialement,', '{"%u":"Nom d''utilisateur","%candidat":"Nom du candidat","%questionnaire":"Questionnaire"}'),
(29, '[HOPITALNUMERIQUE] - Notifications requetes', 'Mail de notification des changements apportées aux requetes', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\r\n\r\nLes résultats de votre requête <b>%requete</b> viennent d''être mis à jour.\r\n\r\nLes publications suivantes ont été ajoutées :\r\n%nouvellespublications\r\nLes publications suivantes ont été mises à jour :\r\n%misesajourpublications\r\n\r\n\r\nCordialement,', '{"%u":"Nom d''utilisateur","%requete":"Nom de la requete ","%nouvellespublications":"Liste des nouvelles publications","%misesajourpublications":"Liste des publications mises à jour"}');
