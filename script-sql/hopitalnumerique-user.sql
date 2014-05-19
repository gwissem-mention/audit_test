/* Modification du nombre de connexions des users */
UPDATE core_user SET usr_nb_visite = 1 WHERE usr_last_login is not null;

/* Modif des ressources sur la desinscription (19/05/2014) */
/* 16:55:10 Gaia (localnodevo) */ UPDATE `core_ressource` SET `res_pattern` = '/^\\/compte-hn\\/(informations-personnelles|mot-de-passe|desinscription)/' WHERE `res_id` = '18';