/* 11:48:58 Gaia (localnodevo) */ UPDATE `core_menu_item` SET `itm_route` = 'ccdn_forum_user_category_index', `itm_uri` = '' WHERE `itm_id` = '67';

INSERT INTO `core_mail` (`mail_id`, `mail_objet`, `mail_description`, `mail_expediteur_mail`, `mail_expediteur_name`, `mail_body`, `mail_params`)
VALUES
    (36, '[HOPITALNUMERIQUE] - Nouveau message sur le forum', 'Nouveau message sur le forum', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u,\n\nUn nouveau message a été posté sur un forum d’Hôpital Numérique :\n- Forum : %forum\n- Catégorie : %categorie\n- Thème : %theme\n- Fil de discussion : %fildiscusssion\n- Lien vers le message : %lienversmessage\n\n\nCordialement,', '{\"%u\":\"Nom d\'utilisateur\",\"%forum\":\"Nom du forum sur lequel le nouveau message est posté.\",\"%categorie\":\"Nom de la catégorie à la laquelle le message a été posté.\",\"%fildiscusssion\":\"Titre du fil de discussion.\", \"%lienversmessage\":\"URL vers le nouveau message posté.\"}');


--- 
/* 08/07/14 */
/* 09:36:48 Gaia (localnodevo) */ UPDATE `core_mail` SET `mail_body` = 'Bonjour %user,\n\nUn nouveau message a été posté sur un forum d’Hôpital Numérique :\n<ul><li>Forum : %forum</li>\n<li>Catégorie : %categorie</li>\n<li>Thème : %theme</li>\n<li>Fil de discussion : %fildiscusssion</li>\n<li>Lien vers le message : %lienversmessage</li></ul>\n\nCordialement,' WHERE `mail_id` = '36';
