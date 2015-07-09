/* 11:29:09 HN */ 
UPDATE `core_mail` 
SET 
    `mail_objet` = '[ANAP] - Mise à jour d\'une publication',
    `mail_description` = 'Mail de notification d\'une mise à jour de notification', 
    `mail_body` = 'Bonjour %u,\r\n\r\nLa publication %titrepublication vient d\'être mise à jour.\r\n\r\nCordialement,' 
WHERE `mail_id` = '29';
