-- Template mails
UPDATE core_mail
SET mail_body =
'Bonjour %prenomUtilisateur,<br /><br />

%prenomUtilisateur2 %nomUtilisateur2 vient de poster une réponse dans la discussion "%discussionName" :
<a href="%urlDiscussion">%urlDiscussion</a><br /><br />

<a href="%urlUnfollow">Ne plus suivre cette discussion</a> ; <a href="%manageAlerts">Changer la fréquence de mes alertes</a><br /><br />

Cordialement,',
mail_params =
'{"%prenomUtilisateur2":"Prénom du rédacteur", "%nomUtilisateur2":"Nom du rédacteur", "%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 100;


UPDATE core_mail
SET mail_body =
'Bonjour, <br /><br />

%nomUtilisateur %prenomUtilisateur vient de poster une réponse nécessitant une modération dans la discussion "%discussionName" :
<a href="%urlDiscussion">%urlDiscussion</a><br /><br />

<a href="%urlUnfollow">Ne plus suivre cette discussion</a> ; <a href="%manageAlerts">Changer la fréquence de mes alertes</a><br /><br />

Cordialement,',
mail_params =
'{"%prenomUtilisateur2":"Prénom du rédacteur", "%nomUtilisateur2":"Nom du rédacteur", "%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 101;

-- Delete forum parameter
DELETE FROM hn_notification_user_settings WHERE notificationCode IN ('forum_post_created', 'forum_topic_created');
DELETE FROM hn_notification WHERE notificationCode IN ('forum_post_created', 'forum_topic_created');
