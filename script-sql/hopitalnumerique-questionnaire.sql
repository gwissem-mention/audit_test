
INSERT INTO `hn_questionnaire_question` (`que_id`, `qst_id`, `typ_question`, `que_libelle`, `que_obligatoire`, `que_verifJS`, `que_ordre`, `que_alias`, `que_reference_param_tri`, `que_choixpossibles`)
VALUES
    (NULL, 2, 1, 'Téléphone de votre directeur', 1, 'validate[minSize[10],maxSize[10]],custom[phone]', 5, 'telephone_directeur', NULL, NULL),
    (NULL, 2, 1, 'Libellé contact administratif', 1, NULL, 6, 'libelle_contact_administratif', NULL, NULL),
    (NULL, 2, 1, 'Nom contact administratif', 1, NULL, 7, 'nom_contact_administratif', NULL, NULL);
