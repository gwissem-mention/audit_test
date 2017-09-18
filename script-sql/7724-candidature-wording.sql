/** Ambassador **/
UPDATE hn_questionnaire_question SET que_libelle = "Votre disponibilité : 10 demi-journées par an pour votre activité dans le réseau" WHERE que_id = 20;
UPDATE hn_questionnaire_question SET que_libelle = "Dans quel(s) domaines(s) avez vous mené des projets ?" WHERE que_id = 54;
UPDATE hn_questionnaire_question SET que_libelle = "Quels projets numériques marquants avez-vous conduit ?" WHERE que_id = 55;
DELETE FROM hn_questionnaire_question WHERE que_id IN (212, 17, 18, 36, 37, 38);
INSERT INTO hn_questionnaire_question (qst_id, typ_question, que_libelle, que_obligatoire, que_ordre, que_alias) VALUE (2, 3, "Ajouter votre CV", 1, 10, "cv");


/** Expert **/
UPDATE hn_questionnaire_question SET que_libelle = "Parlez-nous de votre expertise reconnue et démontrable dans les projets numériques en santé" WHERE que_id = 2;
UPDATE hn_questionnaire_question SET que_libelle = "Pouvez-vous nous présenter en quelques phrases votre vision du développement de l'usage du numérique en santé ?" WHERE que_id = 12;
UPDATE hn_questionnaire_question SET que_libelle = "Quelles sont pour vous les principales difficultés que les structures vont rencontrer dans l'usage du numérique en santé ?" WHERE que_id = 13;
DELETE FROM hn_questionnaire_question WHERE que_id BETWEEN 5 AND 11;
INSERT INTO hn_questionnaire_question (qst_id, typ_question, que_libelle, que_obligatoire, que_ordre, que_alias, que_reference_param_tri) VALUE (1, 10, "Sur quels domaines avez-vous le plus de connaissances ?", 0, 5, "fonction", "FONCTION_SI");
INSERT INTO hn_questionnaire_question (qst_id, typ_question, que_libelle, que_obligatoire, que_ordre, que_alias) VALUE (1, 2, "Pouvez-vous nous présenter en quelques phrases votre vision du développement de l'usage du numérique en santé ?", 0, 6, "fonction_description");


INSERT INTO hn_reference_code (reference, label) SELECT ref_id, 'FONCTION_SI' FROM hn_reference_has_parent WHERE ref_parent_id = 2100;
