INSERT INTO hopital_numerique.core_mail (mail_objet, mail_description, mail_expediteur_mail, mail_expediteur_name, mail_body, mail_params, mail_notification_region_referent) VALUES ("Nouvelle notation d'objet", "Nouveau commentaire et note d'un objet", "%mailContactDomaineCurrent", "ANAP - %nomContactDomaineCurrent", "Bonjour,

%nomUtilisateur %prenomUtilisateur vient de mettre la note de %note à <a href="%urlDocument">ce document</a> en laissant ce commentaire :

%comment", "{"%nomUtilisateur":"Nom de l'utilisateur", "%prenomUtilisateur":"Prénom de l'utilisateur", "%urlDocument":"URL du document", "%note":"Note de l'utilisateur", %comment:"Commentiare de l'utilisateur"}", 0);