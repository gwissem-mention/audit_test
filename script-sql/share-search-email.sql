INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`, `mail_notification_region_referent`)
VALUES
  (76, 'Partage de ma recherche', 'Partage de ma recherche', '', '', 'Bonjour,\n\nJe vous partage l\'url de ma recherche %nomRecherche : %urlRecherche\n\r\nCordialement,\n%prenomUtilisateur %nomUtilisateur', '{\"%urlRecherche\":\"URL de la recherche\", \"%nomRecherche\":\"Nom de la recherche\", \"%nomUtilisateur\":\"Nom de l\'utilisateur\", \"%prenomUtilisateur\":\"Prénom de l\'utilisateur\"}', 0);
