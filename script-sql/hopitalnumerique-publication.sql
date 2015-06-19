INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (570, NULL, 'Actualité Ambassadeur', 'CATEGORIE_ARTICLE', 3, 0, 0, 0, 19);
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (53, 'FrontOffice - Actualités ambassadeur', '/^\\/Ambassadeur-actualites/', -1, 2);
