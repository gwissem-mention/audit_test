/* Modification du nombre de connexions des users */
UPDATE core_user SET usr_nb_visite = 1 WHERE usr_last_login is not null;

/* Modif des ressources sur la desinscription (19/05/2014) */
/* 16:55:10 Gaia (localnodevo) */ UPDATE `core_ressource` SET `res_pattern` = '/^\\/compte-hn\\/(informations-personnelles|mot-de-passe|desinscription)/' WHERE `res_id` = '18';
UPDATE core_user SET usr_raison_desinscription = NULL; 


/* Questionnaire ambassadeur : GME - 21/05/2014 */
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (420, NULL, 'DPI Interopérable', 'DOMAINE_FONCTIONNEL_CANDIDATURE', 3, 0, 0, 1, 1),
    (421, NULL, 'Prescription électronique / plan de soins', 'DOMAINE_FONCTIONNEL_CANDIDATURE', 3, 0, 0, 1, 2),
    (422, NULL, 'Résultats d’imagerie, biologie, anapath', 'DOMAINE_FONCTIONNEL_CANDIDATURE', 3, 0, 0, 1, 3),
    (423, NULL, 'Programmation des ressources', 'DOMAINE_FONCTIONNEL_CANDIDATURE', 3, 0, 0, 1, 4),
    (424, NULL, 'Pilotage médico-économique', 'DOMAINE_FONCTIONNEL_CANDIDATURE', 3, 0, 0, 1, 5);
