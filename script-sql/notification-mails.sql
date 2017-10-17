SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

UPDATE `core_mail` SET
  `mail_objet` = '[ANAP] - Mise à jour d\'une publication',
  `mail_description` = 'Mail de notification d\'une mise à jour',
  `mail_body` = 'Bonjour %prenomUtilisateur,\r\n\r\nLa publication <a href=\"%urlPublication\">&quot;%titrePublication&quot;</a> vient d\'être mise à jour.\r\n\r\nDétail de la mise à jour :\r\n%miseAJour\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  `mail_params` = '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%titrePublication\":\"Titre de la publication\", \"%miseAJour\":\"Détail de ma mise à jour\", \"%urlPublication\":\"Lien vers la publication\"}'
WHERE `core_mail`.`mail_id` = 29;

UPDATE `core_mail` SET
  `mail_objet` = '[ANAP] - Nouveau message sur le forum',
  `mail_description` = 'Nouveau message sur le forum',
  `mail_body` = 'Bonjour %prenomUtilisateur,\r\n\r\nUn nouveau message vient d\'être posté par %pseudoAuteur sur le forum <a href=\"%urlMessage\">&quot;%forum &gt; %categorie &gt; %theme &gt; %fildiscusssion&quot</a>.\r\n\r\nMessage posté :\r\n%message\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  `mail_params` = '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%pseudoAuteur\":\"Pseudo de l\'auteur\", \"%forum\":\"Nom du forum sur lequel le message a été posté.\", \"%categorie\":\"Nom de la catégorie dans la laquelle le message a été posté.\", \"%theme\":\"Nom du thème dans lequel le message a été posté.\", \"%fildiscusssion\":\"Titre du fil de discussion.\", \"%message\":\"Contenu du message\", \"%urlMessage\":\"Lien vers le nouveau message posté.\"}'
WHERE `core_mail`.`mail_id` = 36;

UPDATE `core_mail` SET
  `mail_objet` = '[ANAP] - Nouveau commentaire sur une publication',
  `mail_description` = 'Mail d\'alerte lors de la soumission d\'un nouveau commentaire sur une publication',
  `mail_body` = 'Bonjour %prenomUtilisateur,\r\n\r\nUn nouveau commentaire vient d\'être posté sur <a href=\"%urlPublication\">&quot;%titrePublication&quot;</a>.\r\n\r\nCommentaire posté :\r\n%commentaire\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  `mail_params` = '{\"%titrePublication\":\"Titre de la publication\", \"%commentaire\":\"Contenu du commentaire\", \"%urlPublication\":\"Lien vers la publication\"}'
WHERE `core_mail`.`mail_id` = 69;

UPDATE `core_mail` SET
  `mail_objet` = '[ANAP] - Nouveau commentaire sur un groupe',
  `mail_description` = 'Nouveau commentaire sur un groupe la communauté de pratique',
  `mail_body` = 'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de poster un nouveau commentaire sur le groupe <a href=\"%urlGroupe\">&quot;%nomGroupe&quot;</a>.\r\n\r\nCommentaire ajouté :\r\n%commentaire\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  `mail_params` = '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de l\'auteur du commentaire\", \"%nomUtilisateurDist\":\"Nom de l\'auteur du commentaire\", \"%nomGroupe\":\"Titre du groupe\", \"%commentaire\":\"Contenu du commentaire\", \"%urlGroupe\":\"Lien vers le groupe\"}'
WHERE `core_mail`.`mail_id` = 71;

UPDATE `core_mail` SET
  `mail_objet` = '[ANAP] - Nouveau commentaire sur une fiche d\'un groupe',
  `mail_description` = 'Nouveau commentaire sur une fiche de la communauté de pratique',
  `mail_body` = 'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de poster un nouveau commentaire sur la fiche <a href=\"%urlFiche\">&quot;%nomFiche&quot;</a> du groupe &quot;%nomGroupe&quot;.\r\n\r\nCommentaire ajouté :\r\n%commentaire\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  `mail_params` = '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de l\'auteur du commentaire\", \"%nomUtilisateurDist\":\"Nom de l\'auteur du commentaire\", \"%nomFiche\":\"Titre de la fiche\", \"%nomGroupe\":\"Nom du groupe\", \"%urlFiche\":\"Lien vers la fiche\"}'
WHERE `core_mail`.`mail_id` = 72;

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  75,
  '[ANAP] - Mise à jour d\'un autodiagnostic',
  'Mise à jour notifiée d\'un autodiagnostic',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\nL\'autodiagnostic <a href=\"%urlAutodiagnostics\">&quot;%nomautodiag&quot;</a> vient d\'être mis à jour par l\'ANAP.\r\n\r\nDétail de la mise à jour :\r\n%miseAJour\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%nomautodiag\": \"Nom de l\'autodiagnostic\", \"%miseAJour\":\"Contenu de la mise à jour\", \"%urlAutodiagnostics\":\"Lien vers la page mes services / mes autodiagnostics.\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  88,
  '[ANAP] - Mise à jour d\'un parcours guidé',
  'Mise à jour notifiée d\'un parcours guidé',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\nLe parcours guidé <a href=\"%urlParcoursGuides\">&quot;%nomParcours&quot;</a> vient d\'être mis à jour par l\'ANAP.\r\n\r\nDétail de la mise à jour :\r\n%miseAJour\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%nomParcours\": \"Nom du parcours guidé\", \"%miseAJour\":\"Contenu de la mise à jour\", \"%urlParcoursGuides\":\"Lien vers la page mes services / mes parcours guidés.\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  89,
  '[ANAP] - Modification d\'un rapport partagé',
  'Modification d\'un rapport partagé',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de modifier le rapport <a href=\"%urlMonPanier\">&quot;%nomRapport&quot;</a>.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%prenomUtilisateurDist\":\"Prénom auteur de la mise à jour\", \"%nomUtilisateurDist\":\"Nom auteur de la mise à jour\", \"%nomRapport\": \"Nom du rapport\", \"%urlMonPanier\":\"Lien vers la page mon panier.\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  78,
  '[ANAP] - Partage d\'un nouveau rapport avec vous',
  'Partage d\'un nouveau rapport',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de vous partager son rapport <a href=\"%urlMonPanier\">&quot;%nomRapport&quot;</a>.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de l\'utilisateur à l\'origine du partage\", \"%nomUtilisateurDist\":\"Nom de l\'utilisateur à l\'origine du partage\", \"%nomRapport\":\"nom du rapport\", \"%urlMonPanier\":\"Lien vers la page mon panier.\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  79,
  '[ANAP] - Partage d\'un rapport avec d\'autres utilisateurs',
  'Partage d\'un rapport avec d\'autres utilisateurs',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de partager le rapport <a href=\"%urlMonPanier\">&quot;%nomRapport&quot;</a> avec %prenomUtilisateurTo %nomUtilisateurTo.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de l\'utilisateur à l\'origine du partage\", \"%nomUtilisateurDist\":\"Nom de l\'utilisateur à l\'origine du partage\", \"%prenomUtilisateurTo\":\"Prénom de l\'utilisateur destinataire du partage\", \"%nomUtilisateurTo\":\"Nom de l\'utilisateur destinataire du partage\", \"%nomRapport\":\"nom du rapport\", \"%urlMonPanier\":\"Lien vers la page mon panier.\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  80,
  '[ANAP] - Copie d\'un nouveau rapport',
  'Copie d\'un nouveau rapport',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de vous copier son rapport <a href=\"%urlMonPanier\">&quot;%nomRapport&quot;</a>.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de l\'utilisateur à l\'origine de la copie\", \"%nomUtilisateurDist\":\"Nom de l\'utilisateur à l\'origine de la copie\", \"%nomRapport\":\"nom du rapport\", \"%urlMonPanier\":\"Lien vers la page mon panier.\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  81,
  '[ANAP] - Nouvelle discussion sur le forum',
  'Nouvelle discussion sur le forum',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\nUne nouvelle discussion vient d\'être proposée par %pseudoAuteur sur le forum <a href=\"%urlMessage\">&quot;%forum &gt; %categorie &gt; %theme &gt; %fildiscusssion&quot;</a>.\r\n\r\nMessage posté :\r\n%message\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom utilisateur\", \"%pseudoAuteur\":\"Pseudo de l\'auteur\", \"%forum\":\"Nom du forum sur lequel la discussion a été ouverte.\", \"%categorie\":\"Nom de la catégorie dans la laquelle la discussion a été ouverte.\", \"%theme\":\"Nom du thème dans lequel la discussion a été ouverte.\", \"%fildiscusssion\":\"Titre du fil de discussion.\", \"%message\":\"Contenu du message\", \"%urlMessage\":\"Lien vers le nouveau message posté.\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  82,
  '[ANAP] - Nouveau document dans un groupe',
  'Nouveau document dans un groupe de la communauté',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de poster un nouveau fichier (&quot;%nomFichier&quot;) dans le groupe <a href=\"%urlGroupe\">&quot;%nomGroupe&quot;</a>.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de la personne ayant déposé le fichier\", \"%nomUtilisateurDist\":\"Nom de la personne ayant déposé le fichier\", \"%nomGroupe\":\"Titre du groupe\", \"%nomFichier\":\"Nom du fichier\", \"%urlGroupe\":\"Lien vers le groupe\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  83,
  '[ANAP] - Nouvelle personne dans un groupe',
  'Nouvelle personne dans un groupe de la communauté',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de s\'inscrire au groupe <a href=\"%urlGroupe\">&quot;%nomGroupe&quot;</a>.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de la personne ayant déposé le fichier\", \"%nomUtilisateurDist\":\"Nom de la personne ayant déposé le fichier\", \"%nomGroupe\":\"Titre du groupe\", \"%urlGroupe\":\"Lien vers le groupe\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  84,
  '[ANAP] - Nouveau groupe dans la communauté',
  'Nouveau groupe dans la communauté',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\nUn nouveau groupe <a href=\"%urlCommunaute\">&quot;%nomGroupe&quot;</a> vient d\'être créé par l\'ANAP. La date de démarrage est prévue pour le %dateDebut.\r\n\r\nDescription du groupe :\r\n%description\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%dateDebut\":\"Date de démarrage du groupe\", \"%nomGroupe\":\"Titre du groupe\", \"%description\":\"Description du groupe\", \"%urlCommunaute\":\"Lien vers la communauté\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  85,
  '[ANAP] - Nouvelle personne dans la communauté',
  'Nouvelle personne dans la communauté',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%prenomUtilisateurDist %nomUtilisateurDist vient de s\'inscrire à la communauté de pratique.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%prenomUtilisateurDist\":\"Prénom du nouveau membre de la communauté\", \"%nomUtilisateurDist\":\"Nom du nouveau membre de la communauté\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  86,
  '[ANAP] - Nouvelle personne ressource dans votre région',
  'Nouvelle personne ressource dans votre région',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\nUn nouvelle personne ressource est disponible dans votre région : %prenomUtilisateurDist %nomUtilisateurDist en tant que &quot;%role&quot;.\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%prenomUtilisateurDist\":\"Prénom de la personne ressource\", \"%nomUtilisateurDist\":\"Nom de la personne ressource\", \"%role\":\"Titre du rôle de la personne ressource\"}',
  '0'
);

INSERT INTO `core_mail` (
  `mail_id`,
  `mail_objet`,
  `mail_description`,
  `mail_expediteur_mail`,
  `mail_expediteur_name`,
  `mail_body`,
  `mail_params`,
  `mail_notification_region_referent`
) VALUES (
  87,
  '[ANAP] - Prochaines sessions de montée en compétences dans les 3 mois à venir',
  'Prochaines sessions de montée en compétences dans les 3 mois à venir',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\nListe des prochaines sessions de montée en compétences dans les 3 mois à venir :\r\n\r\n%liste\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{\"%prenomUtilisateur\":\"Prénom de l\'utilisateur\", \"%liste\":\"Liste des sessions de formation de type MAPF\"}',
  '0'
);

INSERT INTO core_mail (
  mail_id,
  mail_objet,
  mail_description,
  mail_expediteur_mail,
  mail_expediteur_name,
  mail_body,
  mail_params,
  mail_notification_region_referent
) VALUES (
  90,
  '[ANAP] - Notification de l\'activité',
  'Mail de notification de l\'activité',
  '%mailContactDomaineCurrent',
  'ANAP - %nomContactDomaineCurrent',
  'Bonjour %prenomUtilisateur,\r\n\r\n%message\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP',
  '{"%prenomUtilisateur":"Prénom utilisateur", "%message":"Liste des notifications"}',
  '0'
);

UPDATE `core_mail` SET
  `mail_body` = 'Bonjour %prenomUtilisateur,\r\n\r\nUne nouvelle discussion vient d\'être proposée par %pseudoAuteur sur le forum <a href=\"%urlMessage\">&quot;%forum &gt; %categorie &gt; %theme &gt; %fildiscusssion&quot;</a>.\r\n\r\nMessage posté :\r\n%message\r\n\r\nCordialement,\r\n\r\nL\'équipe ANAP'
WHERE `core_mail`.`mail_id` = 81;

COMMIT;
