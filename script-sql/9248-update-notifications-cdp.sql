-- Template mails
UPDATE core_mail
SET mail_body =
'Bonjour,

%nomUtilisateur %prenomUtilisateur vient de poster une réponse dans la discussion "%discussionName" :
<a href="%urlDiscussion">%urlDiscussion</a>

<a href="%urlUnfollow">Ne plus suivre cette discussion</a> ; <a href="%manageAlerts">Changer la fréquence de mes alertes</a>

Cordialement,',
mail_params =
'{"%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 100;


UPDATE core_mail
SET mail_body =
'Bonjour,

%nomUtilisateur %prenomUtilisateur vient de poster une réponse nécessitant une modération dans la discussion "%discussionName" :
<a href="%urlDiscussion">%urlDiscussion</a>

<a href="%urlUnfollow">Ne plus suivre cette discussion</a> ; <a href="%manageAlerts">Changer la fréquence de mes alertes</a>

Cordialement,',
mail_params =
'{"%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 101;

-- Delete forum parameter
DELETE FROM hn_notification_user_settings WHERE notificationCode IN ('forum_post_created', 'forum_topic_created');
DELETE FROM hn_notification WHERE notificationCode IN ('forum_post_created', 'forum_topic_created');
