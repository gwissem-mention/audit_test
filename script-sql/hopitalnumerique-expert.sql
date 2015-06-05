INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (530, NULL, 'Cycle annuel de collèges d\'experts', 'ACTIVITE_TYPE', 3, 0, 0, 0, 1),
    (531, NULL, 'Groupe de travail', 'ACTIVITE_TYPE', 3, 0, 0, 0, 2),
    (532, NULL, 'Avis d\'experts', 'ACTIVITE_TYPE', 3, 0, 0, 0, 3);


INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (535, NULL, 'Deloite', 'PRESTATAIRE', 3, 0, 0, 0, 1),
    (536, NULL, 'Sanexis', 'PRESTATAIRE', 3, 0, 0, 0, 2),
    (537, NULL, 'Columbus', 'PRESTATAIRE', 3, 0, 0, 0, 3);

INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (540, NULL, 'UO 1 : Support aux experts', 'UO_PRESTATAIRE', 3, 0, 0, 0, 1),
    (541, NULL, 'UO 2 : REX', 'UO_PRESTATAIRE', 3, 0, 0, 0, 2),
    (542, NULL, 'UO 3 : CAPI', 'UO_PRESTATAIRE', 3, 0, 0, 0, 3),
    (543, NULL, 'UO 4 : Guide', 'UO_PRESTATAIRE', 3, 0, 0, 0, 4),
    (544, NULL, 'UO 5 : Démarche', 'UO_PRESTATAIRE', 3, 0, 0, 0, 5),
    (545, NULL, 'UO 6 : Outil', 'UO_PRESTATAIRE', 3, 0, 0, 0, 6),
    (546, NULL, 'UO 7 : Expertise en propre', 'UO_PRESTATAIRE', 3, 0, 0, 0, 7);

INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (550, NULL, 'En cours', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 1),
    (551, NULL, 'Validé', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 2),
    (552, NULL, 'Terminé', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 3),
    (553, NULL, 'Présenté', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 4);


INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (560, NULL, '0', 'MONTANT_VACATION', 3, 0, 0, 0, 1);
