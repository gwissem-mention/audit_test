UPDATE core_mail SET mail_body = 'Bonjour,

Je vous invite à rejoindre la communauté de pratique %subjectDomaine pour échanger autour de nos expériences.
Rejoignez notre communauté et choisissez un groupe thématique %nomGroupe en 3 étapes :
1. Rendez-vous sur <a href=''%cdpArticleUrl''>%cdpArticleUrl</a> et connectez vous. Si vous n''avez pas de compte, saisissez votre adresse mail dans la partie "Créer son compte" et enregistrez les informations demandées (nom, prénom, établissement, fonction...)
2. Enregistrez vos informations et cliquez sur "Rejoindre la communauté"
3. Rejoignez le groupe thématique qui vous intéresse dans la partie "Groupes thématiques".

Bravo, vous avez désormais rejoint les autres membres du groupe %nomGroupe. D''ici quelques heures, l''animateur du groupe aura validé votre participation et vous pourrez échanger documents et discussions avec vos pairs.

En cas de question ou de difficulté technique, contactez <a href=''mailto:%contactMail''>%contactMail</a>.

Cordialement,

%u', mail_params = '{"%u":"Expéditeur", "%nomGroupe":"Nom du groupe", "%u":"Nom de l''expéditeur", "%contactMail": "Mail de contact du domaine", "%cdpArticleUrl": "Lien vers l''article de la CDP"}' WHERE mail_id = 67;
