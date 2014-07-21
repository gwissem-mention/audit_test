/* 14:08:20 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Liste déroulante' WHERE `typ_id` = '5';
/* 14:09:38 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Liste déroulante à choix multiples' WHERE `typ_id` = '10';
/* 14:10:40 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Bouttons radios' WHERE `typ_id` = '8';
/* 14:12:07 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Case à cocher' WHERE `typ_id` = '4';
/* 14:12:56 Gaia (localnodevo) */ UPDATE `hn_questionnaire_type_question` SET `nom` = 'Ajout de fichier' WHERE `typ_id` = '3';
/* 15:27:37 Gaia (localnodevo) */ INSERT INTO `hn_questionnaire_type_question` (`typ_id`, `libelle`, `nom`) VALUES (NULL, 'entitycheckbox', 'Cases à cocher à choix multiples');
