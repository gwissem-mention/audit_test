
-- Add new question to form 'expert'
INSERT INTO hn_questionnaire_question (qst_id, typ_question, que_libelle, que_obligatoire, que_verifJS, que_ordre, que_alias, que_reference_param_tri) VALUES
  (1, 10, 'Quel(s) rôle(s) vous intéressent ? En choisir 2 maximum.', 0, '', 8, 'quels-roles-vous-interessent-en-choisir-2-maximum', 'ROLE_RESEAU');

-- Remove question 'Pouvez-vous nous présenter en quelques phrases votre vision ...'
DELETE FROM hn_questionnaire_question WHERE que_id = 12;

-- Update sort rank order for questions
UPDATE hn_questionnaire_question SET que_ordre = 6 WHERE que_id = 13;
UPDATE hn_questionnaire_question SET que_ordre = 7 WHERE que_id = 14;
UPDATE hn_questionnaire_question SET que_ordre = 9 WHERE que_id = 15;
UPDATE hn_questionnaire_question SET que_ordre = 10 WHERE que_id = 23;

-- Update label for question 'Parlez-nous de votre expertise reconnue et démontrable dans les projets numériques en santé'
UPDATE hn_questionnaire_question SET que_libelle = 'Parlez-nous de votre expertise reconnue et démontrable dans les projets en santé' WHERE que_id = 2;

-- Update label for question 'Présentez-nous une ou deux de vos expériences réussies dans la mise en place de projets d'infomatisation complexes'
UPDATE hn_questionnaire_question SET que_libelle = 'Présentez-nous une ou deux de vos expériences réussies dans la mise en place de projets complexes' WHERE que_id = 3;

-- Update label for question 'Quelles sont pour vous les principales difficultés que les structures vont rencontrer dans l'usage du numérique en santé ?'
UPDATE hn_questionnaire_question SET que_libelle = 'Quelles sont pour vous les principales difficultés que les structures vont rencontrer ?' WHERE que_id = 13;
