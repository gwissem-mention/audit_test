/* 10:06:30 HN */ UPDATE `core_mail` SET `mail_body` = 'Bonjour %user,\r\n\r\nUn nouveau message a été posté sur un forum d’Hôpital Numérique par %pseudouser :\r\n- Forum : %forum\r\n- Catégorie : %categorie\r\n- Thème : %theme\r\n- Fil de discussion : %fildiscusssion\r\n- Lien vers le message : %lienversmessage\r\n\r\nCordialement,' WHERE `mail_id` = '36';

INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES
    (51, '[%subjectDomaine] - Nouveau message sur le forum à moderer', 'Nouveau message sur le forum en attente de modération', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %user,\r\n\r\nUn nouveau message a été posté sur un forum d’Hôpital Numérique par %pseudouser :\r\n- Forum : %forum\r\n- Catégorie : %categorie\r\n- Thème : %theme\r\n- Fil de discussion : %fildiscusssion\r\n- Lien vers le message : %lienversmessage\r\nCe post contient un lien et est en attente de modération.\r\n\r\nCordialement,', '{\"%user\":\"Nom d\'utilisateur\",\"%forum\":\"Nom du forum sur lequel le nouveau message est posté.\",\"%categorie\":\"Nom de la catégorie à la laquelle le message a été posté.\",\"%fildiscusssion\":\"Titre du fil de discussion.\", \"%lienversmessage\":\"URL vers le nouveau message posté.\"}');
/* 08:48:56 HN */ UPDATE `core_mail` SET `mail_body` = 'Bonjour,\r\n\r\nUn message a été posté ou modifier sur un forum d’Hôpital Numérique :\r\n- Forum : %forum\r\n- Catégorie : %categorie\r\n- Thème : %theme\r\n- Fil de discussion : %fildiscusssion\r\n- Lien vers le message : %lienversmessage\r\nCe post contient un lien et est en attente de modération.\r\n\r\nCordialement,' WHERE `mail_id` = '51';









/* 11:27:22 HN */ UPDATE `core_mail` SET `mail_body` = 'Bonjour,\r\n\r\nUn message a été posté ou modifié sur un forum d’Hôpital Numérique par %pseudouser :\r\n- Forum : %forum\r\n- Catégorie : %categorie\r\n- Thème : %theme\r\n- Fil de discussion : %fildiscusssion\r\n- Lien vers le message : %lienversmessage\r\nCe post contient un lien et est en attente de modération.\r\n\r\nCordialement,' WHERE `mail_id` = '51';




