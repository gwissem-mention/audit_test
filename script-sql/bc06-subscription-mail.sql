INSERT INTO hn_develop.core_mail (mail_id, mail_objet, mail_description, mail_expediteur_mail, mail_expediteur_name, mail_body, mail_params, mail_notification_region_referent) VALUES (81, '[%subjectDomaine] - Nouvelle réponse dans la communauté de pratique', 'Réponse dans une discussion de la communauté de pratique', '%mailContactDomaineCurrent', 'ANAP - %nomContactDomaineCurrent', 'Bonjour,

%nomUtilisateur %prenomUtilisateur vient de poster une réponse dans la discussion "%discussionName" :
%urlDiscussion

Cordialement,', '{"%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion"}', 0);
