UPDATE core_mail
SET mail_body =
'Bonjour,

%nomUtilisateur %prenomUtilisateur vient de poster une réponse dans la discussion "%discussionName" :
%urlDiscussion

<a href="%urlUnfollow">Ne plus suivre cette discussion</a>
<a href="%manageAlerts">Gérer mes alertes</a>

Cordialement,',
mail_params =
'{"%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 100;


UPDATE core_mail
SET mail_body =
'Bonjour,

%nomUtilisateur %prenomUtilisateur vient de poster une réponse nécessitant une modération dans la discussion "%discussionName" :
%urlDiscussion

<a href="%urlUnfollow">Ne plus suivre cette discussion</a>
<a href="%manageAlerts">Gérer mes alertes</a>

Cordialement,',
mail_params =
'{"%nomUtilisateur":"Nom de l''utilisateur", "%prenomUtilisateur":"Prénom de l''utilisateur", "%discussionName":"Nom de la discussion", "%urlDiscussion":"URL de la discussion", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 101;
