/** Ambassador **/
UPDATE hn_questionnaire_question SET que_libelle = "Votre disponibilité : 10 demi-journées par an pour votre activité dans le réseau" WHERE que_id = 20;
UPDATE hn_questionnaire_question SET que_libelle = "Dans quel(s) domaines(s) avez vous mené des projets ?" WHERE que_id = 54;
UPDATE hn_questionnaire_question SET que_libelle = "Quels projets numériques marquants avez-vous conduit ?" WHERE que_id = 55;
DELETE FROM hn_questionnaire_question WHERE que_id IN (212, 17, 18, 36, 37, 38);
INSERT INTO hn_questionnaire_question (qst_id, typ_question, que_libelle, que_obligatoire, que_ordre, que_alias) VALUE (2, 3, "Ajouter votre CV", 1, 15, "cv");


/** Expert **/
