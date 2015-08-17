/* 09:57:28 HN */ UPDATE `core_mail` SET `mail_expediteur_mail` = '%mailContactDomaineCurrent', `mail_expediteur_name` = 'ANAP - %nomContactDomaineCurrent' WHERE `mail_id` != '50';

/* 10:05:01 HN */ UPDATE `core_mail` SET `mail_body` = 'Bonjour %u,\r\n\r\nL\'Anap vient de vous inscrire sur la plateforme \"%nomContactDomaineCurrent\".\r\n\r\nVous pouvez vous connecter sur le site %s en entrant votre adresse mail et votre mot de passe : %p\r\n\r\nCordialement,' WHERE `mail_id` = '1';

