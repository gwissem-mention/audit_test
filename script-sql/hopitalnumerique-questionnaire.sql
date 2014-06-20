/* 10:38:09 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `libelle` = 'entityradio' WHERE `typ_id` = '8';

INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (NULL, NULL, 'Insatisfaisant', 'EVALUATION_CRITERE_NOTE', 3, 0, 0, 1, 1);
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (NULL, NULL, 'Peu satisfaisant', 'EVALUATION_CRITERE_NOTE', 3, 0, 0, 1, 2);
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (NULL, NULL, 'Plutôt satisfaisant', 'EVALUATION_CRITERE_NOTE', 3, 0, 0, 1, 3);
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (NULL, NULL, 'Très satisfaisant', 'EVALUATION_CRITERE_NOTE', 3, 0, 0, 1, 4);


/* 10:43:31 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '39';
/* 10:43:32 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '40';
/* 10:43:33 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '42';
/* 10:43:34 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '41';
/* 10:43:35 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '43';
/* 10:43:36 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '44';
/* 10:43:37 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '45';
/* 10:43:38 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '46';
/* 10:43:39 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '47';
/* 10:43:40 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '48';
/* 10:43:41 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '49';
/* 10:43:42 Gaia (localnodevo) */ UPDATE `hn_questionnaire_question` SET `que_reference_param_tri` = 'EVALUATION_CRITERE_NOTE' WHERE `que_id` = '50';