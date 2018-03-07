INSERT INTO core_mail(mail_id, mail_objet, mail_description, mail_expediteur_mail, mail_expediteur_name, mail_body, mail_params, mail_notification_region_referent)
VALUES (
	102,
	'[ANAP] - Nouvelle discussion dans un de mes groupes',
	'Nouvelle discussion dans un de mes groupes',
	'%mailContactDomaineCurrent',
	'ANAP - %nomContactDomaineCurrent',
	'Bonjour,<br /><br />La discussion <a href="%urlDiscussion">%nomDiscussion</a> vient d''être créée dans le groupe <a href="%urlGroupe">%nomGroupe</a>.<br /><br />Cordialement,<br /><br />L''équipe ANAP',
	'{"%nomDiscussion":"Titre de la discussion", "%urlDiscussion":"Lien vers la discussion", "%nomGroupe":"Titre du groupe", "%urlGroupe":"Lien vers le groupe"}',
	0
);

INSERT INTO core_mail(mail_id, mail_objet, mail_description, mail_expediteur_mail, mail_expediteur_name, mail_body, mail_params, mail_notification_region_referent)
VALUES (
	103,
	'[ANAP] - Nouvelle discussion',
	'Nouvelle discussion',
	'%mailContactDomaineCurrent',
	'ANAP - %nomContactDomaineCurrent',
	'Bonjour,<br /><br />La discussion <a href="%urlDiscussion">%nomDiscussion</a> vient d''être créée.<br /><br />Cordialement,<br /><br />L''équipe ANAP',
	'{"%nomDiscussion":"Titre de la discussion", "%urlDiscussion":"Lien vers la discussion"}',
	0
);
