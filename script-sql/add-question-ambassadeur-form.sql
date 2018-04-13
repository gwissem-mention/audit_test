
-- Add new question to form 'ambassadeur'
INSERT INTO hn_questionnaire_question (qst_id, typ_question, que_libelle, que_obligatoire, que_verifJS, que_ordre, que_alias, que_reference_param_tri) VALUES
(2, 10, 'Quel(s) rôle(s) vous intéressent ? En choisir 2 maximum. <a href="#" target="_blank">En savoir plus.</a>', 0, '', 8, 'quels-roles-vous-interessent-en-choisir-2-maximum', 'ROLE_RESEAU');

-- Update sort rank order for CV question
UPDATE hn_questionnaire_question SET que_ordre = 9 WHERE que_id = 258;

-- Update type for question 'Dans quel(s) domaines(s) avez vous mené des projets ?'
UPDATE hn_questionnaire_question SET typ_question = 2 WHERE que_id = 54;

-- Update label for question 'Quels projets numériques marquants avez-vous conduit ?'
UPDATE hn_questionnaire_question SET que_libelle = 'Quels projets marquants avez-vous conduit ?' WHERE que_id = 55;
