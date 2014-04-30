/* Modification du nombre de connexions des users */
UPDATE core_user SET usr_nb_visite = 1 WHERE usr_last_login is not null;
