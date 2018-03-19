UPDATE core_mail
SET mail_body =
'Bonjour,<br /><br />

La discussion <a href="%urlDiscussion">%nomDiscussion</a> vient d''être créée dans le groupe <a href="%urlGroupe">%nomGroupe</a>.<br /><br />

<a href="%urlUnfollow">Ne plus suivre cette discussion</a> ; <a href="%manageAlerts">Changer la fréquence de mes alertes</a><br /><br />

Cordialement,<br /><br />
L''équipe ANAP',
	mail_params =
'{"%nomDiscussion":"Titre de la discussion", "%urlDiscussion":"Lien vers la discussion", "%nomGroupe":"Titre du groupe", "%urlGroupe":"Lien vers le groupe", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 102;

UPDATE core_mail
SET mail_body =
'Bonjour,<br /><br />

La discussion <a href="%urlDiscussion">%nomDiscussion</a> vient d''être créée.<br /><br />

<a href="%urlUnfollow">Ne plus suivre cette discussion</a> ; <a href="%manageAlerts">Changer la fréquence de mes alertes</a><br /><br />

Cordialement,<br /><br />
L''équipe ANAP',
	mail_params =
	'{"%nomDiscussion":"Titre de la discussion", "%urlDiscussion":"Lien vers la discussion", "%nomGroupe":"Titre du groupe", "%urlGroupe":"Lien vers le groupe", "%urlUnfollow":"URL pour ne plus suivre la discussion", "%manageAlerts":"Gérer les alertes"}'
WHERE mail_id = 103;
