/* Création des nouvelles régions */
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1000', NULL, 'Alsace-Champagne-Ardenne-Lorraine', 'REGION', '3', '0', '0', '1', '1');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1001', NULL, 'Aquitaine-Limousin-Poitou-Charentes', 'REGION', '3', '0', '0', '1', '2');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1002', NULL, 'Auvergne-Rhône-Alpes', 'REGION', '3', '0', '0', '1', '3');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1003', NULL, 'Bourgogne-Franche-Comté', 'REGION', '3', '0', '0', '1', '4');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1004', NULL, 'Bretagne', 'REGION', '3', '0', '0', '1', '5');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1005', NULL, 'Centre-Val de Loire', 'REGION', '3', '0', '0', '1', '6');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1006', NULL, 'Corse', 'REGION', '3', '0', '0', '1', '7');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1007', NULL, 'Guadeloupe', 'REGION', '3', '0', '0', '1', '8');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1008', NULL, 'Guyane', 'REGION', '3', '0', '0', '1', '9');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1009', NULL, 'Île-de-France', 'REGION', '3', '0', '0', '1', '10');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1010', NULL, 'Océan Indien', 'REGION', '3', '0', '0', '1', '11');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1011', NULL, 'Languedoc-Roussillon-Midi-Pyrénées', 'REGION', '3', '0', '0', '1', '12');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1012', NULL, 'Martinique', 'REGION', '3', '0', '0', '1', '13');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1013', NULL, 'Nord-Pas-de-Calais-Picardie', 'REGION', '3', '0', '0', '1', '14');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1014', NULL, 'Normandie', 'REGION', '3', '0', '0', '1', '15');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1015', NULL, 'Pays de la Loire', 'REGION', '3', '0', '0', '1', '16');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES ('1016', NULL, 'Provence-Alpes-Côte d''Azur', 'REGION', '3', '0', '0', '1', '17');

/* Intégration des nouvelles régions */
UPDATE hn_reference SET parent_id = 1000 WHERE ref_code = 'DEPARTEMENT' AND parent_id IN (44, 52, 45);
UPDATE hn_reference SET parent_id = 1001 WHERE ref_code = 'DEPARTEMENT' AND parent_id IN (58, 60, 40);
UPDATE hn_reference SET parent_id = 1002 WHERE ref_code = 'DEPARTEMENT' AND parent_id IN (62, 61);
UPDATE hn_reference SET parent_id = 1003 WHERE ref_code = 'DEPARTEMENT' AND parent_id IN (47, 43);
UPDATE hn_reference SET parent_id = 1004 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 41;
UPDATE hn_reference SET parent_id = 1005 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 49;
UPDATE hn_reference SET parent_id = 1006 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 65;
UPDATE hn_reference SET parent_id = 1007 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 57;
UPDATE hn_reference SET parent_id = 1008 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 55;
UPDATE hn_reference SET parent_id = 1009 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 53;
UPDATE hn_reference SET parent_id = 1010 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 54;
UPDATE hn_reference SET parent_id = 1011 WHERE ref_code = 'DEPARTEMENT' AND parent_id IN (63, 59);
UPDATE hn_reference SET parent_id = 1012 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 56;
UPDATE hn_reference SET parent_id = 1013 WHERE ref_code = 'DEPARTEMENT' AND parent_id IN (46, 51);
UPDATE hn_reference SET parent_id = 1014 WHERE ref_code = 'DEPARTEMENT' AND parent_id IN (48, 50);
UPDATE hn_reference SET parent_id = 1015 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 42;
UPDATE hn_reference SET parent_id = 1016 WHERE ref_code = 'DEPARTEMENT' AND parent_id = 64;
UPDATE core_user SET ref_region = 1000 WHERE ref_region IN (44, 52, 45);
UPDATE core_user SET ref_region = 1001 WHERE ref_region IN (58, 60, 40);
UPDATE core_user SET ref_region = 1002 WHERE ref_region IN (62, 61);
UPDATE core_user SET ref_region = 1003 WHERE ref_region IN (47, 43);
UPDATE core_user SET ref_region = 1004 WHERE ref_region = 41;
UPDATE core_user SET ref_region = 1005 WHERE ref_region = 49;
UPDATE core_user SET ref_region = 1006 WHERE ref_region = 65;
UPDATE core_user SET ref_region = 1007 WHERE ref_region = 57;
UPDATE core_user SET ref_region = 1008 WHERE ref_region = 55;
UPDATE core_user SET ref_region = 1009 WHERE ref_region = 53;
UPDATE core_user SET ref_region = 1010 WHERE ref_region = 54;
UPDATE core_user SET ref_region = 1011 WHERE ref_region IN (63, 59);
UPDATE core_user SET ref_region = 1012 WHERE ref_region = 56;
UPDATE core_user SET ref_region = 1013 WHERE ref_region IN (46, 51);
UPDATE core_user SET ref_region = 1014 WHERE ref_region IN (48, 50);
UPDATE core_user SET ref_region = 1015 WHERE ref_region = 42;
UPDATE core_user SET ref_region = 1016 WHERE ref_region = 64;
UPDATE hn_etablissement SET ref_region = 1000 WHERE ref_region IN (44, 52, 45);
UPDATE hn_etablissement SET ref_region = 1001 WHERE ref_region IN (58, 60, 40);
UPDATE hn_etablissement SET ref_region = 1002 WHERE ref_region IN (62, 61);
UPDATE hn_etablissement SET ref_region = 1003 WHERE ref_region IN (47, 43);
UPDATE hn_etablissement SET ref_region = 1004 WHERE ref_region = 41;
UPDATE hn_etablissement SET ref_region = 1005 WHERE ref_region = 49;
UPDATE hn_etablissement SET ref_region = 1006 WHERE ref_region = 65;
UPDATE hn_etablissement SET ref_region = 1007 WHERE ref_region = 57;
UPDATE hn_etablissement SET ref_region = 1008 WHERE ref_region = 55;
UPDATE hn_etablissement SET ref_region = 1009 WHERE ref_region = 53;
UPDATE hn_etablissement SET ref_region = 1010 WHERE ref_region = 54;
UPDATE hn_etablissement SET ref_region = 1011 WHERE ref_region IN (63, 59);
UPDATE hn_etablissement SET ref_region = 1012 WHERE ref_region = 56;
UPDATE hn_etablissement SET ref_region = 1013 WHERE ref_region IN (46, 51);
UPDATE hn_etablissement SET ref_region = 1014 WHERE ref_region IN (48, 50);
UPDATE hn_etablissement SET ref_region = 1015 WHERE ref_region = 42;
UPDATE hn_etablissement SET ref_region = 1016 WHERE ref_region = 64;
UPDATE hn_questionnaire_reponse SET ref_reference = 1000 WHERE ref_reference IN (44, 52, 45);
UPDATE hn_questionnaire_reponse SET ref_reference = 1001 WHERE ref_reference IN (58, 60, 40);
UPDATE hn_questionnaire_reponse SET ref_reference = 1002 WHERE ref_reference IN (62, 61);
UPDATE hn_questionnaire_reponse SET ref_reference = 1003 WHERE ref_reference IN (47, 43);
UPDATE hn_questionnaire_reponse SET ref_reference = 1004 WHERE ref_reference = 41;
UPDATE hn_questionnaire_reponse SET ref_reference = 1005 WHERE ref_reference = 49;
UPDATE hn_questionnaire_reponse SET ref_reference = 1006 WHERE ref_reference = 65;
UPDATE hn_questionnaire_reponse SET ref_reference = 1007 WHERE ref_reference = 57;
UPDATE hn_questionnaire_reponse SET ref_reference = 1008 WHERE ref_reference = 55;
UPDATE hn_questionnaire_reponse SET ref_reference = 1009 WHERE ref_reference = 53;
UPDATE hn_questionnaire_reponse SET ref_reference = 1010 WHERE ref_reference = 54;
UPDATE hn_questionnaire_reponse SET ref_reference = 1011 WHERE ref_reference IN (63, 59);
UPDATE hn_questionnaire_reponse SET ref_reference = 1012 WHERE ref_reference = 56;
UPDATE hn_questionnaire_reponse SET ref_reference = 1013 WHERE ref_reference IN (46, 51);
UPDATE hn_questionnaire_reponse SET ref_reference = 1014 WHERE ref_reference IN (48, 50);
UPDATE hn_questionnaire_reponse SET ref_reference = 1015 WHERE ref_reference = 42;
UPDATE hn_questionnaire_reponse SET ref_reference = 1016 WHERE ref_reference = 64;
UPDATE hn_contact SET ref_region = 1000 WHERE ref_region IN (44, 52, 45);
UPDATE hn_contact SET ref_region = 1001 WHERE ref_region IN (58, 60, 40);
UPDATE hn_contact SET ref_region = 1002 WHERE ref_region IN (62, 61);
UPDATE hn_contact SET ref_region = 1003 WHERE ref_region IN (47, 43);
UPDATE hn_contact SET ref_region = 1004 WHERE ref_region = 41;
UPDATE hn_contact SET ref_region = 1005 WHERE ref_region = 49;
UPDATE hn_contact SET ref_region = 1006 WHERE ref_region = 65;
UPDATE hn_contact SET ref_region = 1007 WHERE ref_region = 57;
UPDATE hn_contact SET ref_region = 1008 WHERE ref_region = 55;
UPDATE hn_contact SET ref_region = 1009 WHERE ref_region = 53;
UPDATE hn_contact SET ref_region = 1010 WHERE ref_region = 54;
UPDATE hn_contact SET ref_region = 1011 WHERE ref_region IN (63, 59);
UPDATE hn_contact SET ref_region = 1012 WHERE ref_region = 56;
UPDATE hn_contact SET ref_region = 1013 WHERE ref_region IN (46, 51);
UPDATE hn_contact SET ref_region = 1014 WHERE ref_region IN (48, 50);
UPDATE hn_contact SET ref_region = 1015 WHERE ref_region = 42;
UPDATE hn_contact SET ref_region = 1016 WHERE ref_region = 64;
UPDATE hn_facture_remboursement SET ref_region = 1000 WHERE ref_region IN (44, 52, 45);
UPDATE hn_facture_remboursement SET ref_region = 1001 WHERE ref_region IN (58, 60, 40);
UPDATE hn_facture_remboursement SET ref_region = 1002 WHERE ref_region IN (62, 61);
UPDATE hn_facture_remboursement SET ref_region = 1003 WHERE ref_region IN (47, 43);
UPDATE hn_facture_remboursement SET ref_region = 1004 WHERE ref_region = 41;
UPDATE hn_facture_remboursement SET ref_region = 1005 WHERE ref_region = 49;
UPDATE hn_facture_remboursement SET ref_region = 1006 WHERE ref_region = 65;
UPDATE hn_facture_remboursement SET ref_region = 1007 WHERE ref_region = 57;
UPDATE hn_facture_remboursement SET ref_region = 1008 WHERE ref_region = 55;
UPDATE hn_facture_remboursement SET ref_region = 1009 WHERE ref_region = 53;
UPDATE hn_facture_remboursement SET ref_region = 1010 WHERE ref_region = 54;
UPDATE hn_facture_remboursement SET ref_region = 1011 WHERE ref_region IN (63, 59);
UPDATE hn_facture_remboursement SET ref_region = 1012 WHERE ref_region = 56;
UPDATE hn_facture_remboursement SET ref_region = 1013 WHERE ref_region IN (46, 51);
UPDATE hn_facture_remboursement SET ref_region = 1014 WHERE ref_region IN (48, 50);
UPDATE hn_facture_remboursement SET ref_region = 1015 WHERE ref_region = 42;
UPDATE hn_facture_remboursement SET ref_region = 1016 WHERE ref_region = 64;

/* Màj de la facturation */
DELETE FROM hn_facture_remboursement WHERE ref_region = 1000 AND rem_id = 5;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1000 AND rem_id = 13;
UPDATE hn_facture_remboursement SET rem_supplement = 250 WHERE ref_region = 1000 AND rem_id = 6;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1001 AND rem_id = 21;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1001 AND rem_id = 1;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1002 AND rem_id = 23;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1003 AND rem_id = 4;
UPDATE hn_facture_remboursement SET rem_supplement = 250 WHERE ref_region = 1003 AND rem_id = 8;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1011 AND rem_id = 24;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1013 AND rem_id = 12;
DELETE FROM hn_facture_remboursement WHERE ref_region = 1014 AND rem_id = 11;

/* Suppression des anciennes régions */
DELETE FROM `hn_reference` WHERE ref_code = 'REGION' AND ref_id < 1000;



/* #3951 */
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`) VALUES ('1020', NULL, 'Modele_contrat.pdf', 'ACTIVITE_EXPERT_CONTRAT_MODELE', '3', '0', '0', '0', '17'), ('1021', NULL, 'Modele_PV_de_recettes.pdf', 'ACTIVITE_EXPERT_PV_RECETTES_MODELE', '3', '0', '0', '0', '18');
