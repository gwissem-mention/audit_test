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
INSERT INTO `hn_questionnaire_question` (`que_id`, `qst_id`, `typ_question`, `que_libelle`, `que_obligatoire`, `que_verifJS`, `que_ordre`, `que_alias`, `que_reference_param_tri`, `que_choixpossibles`)
VALUES
    (54, 2, 5, 'Dans quel(s) domaines(s) fonctionnel(s) avez vous mené des projets ?', 1, NULL, 9, 'domaine_fonctionnel_projet_menes', 'DOMAINE_FONCTIONNEL_CANDIDATURE', NULL);
INSERT INTO `hn_questionnaire_question` (`que_id`, `qst_id`, `typ_question`, `que_libelle`, `que_obligatoire`, `que_verifJS`, `que_ordre`, `que_alias`, `que_reference_param_tri`, `que_choixpossibles`)
VALUES
    (55, 2, 2, 'Description de votre expérience', 1, NULL, 10, 'description_votre_experience', NULL, NULL);

/* 12:25:01 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_ordre` = '11' WHERE `que_id` = '16';

