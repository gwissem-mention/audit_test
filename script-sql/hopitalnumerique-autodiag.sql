/* Référence des questions */
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`) VALUES (415, NULL, 'Liste déroulante', 'TYPE_QUESTION', '3', '0', '0', '1', '1');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`) VALUES (416, NULL, 'Bouton Radio', 'TYPE_QUESTION', '3', '0', '0', '1', '2');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`) VALUES (417, NULL, 'Champ Texte', 'TYPE_QUESTION', '3', '0', '0', '1', '3');

/* Menu Admin */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(147, NULL, 1, 'Gestion des autodiag', 'hopitalnumerique_autodiag_outil', '[]', NULL, NULL, 'fa fa-bar-chart-o', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 9);

/* Init new Object Field*/
UPDATE `hn_objet` SET `obj_autodiag` = 'a:0:{}';

/* Items de menu pour les autodiag */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 68, 3, 'Autodiagnostics', 'hopitalnumerique_autodiag_front_comptehn', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);

/* Références résultat */
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (418, NULL, 'Initial', 'STATUT_RESULTAT', 3, 0, 0, 0, 1),
    (419, NULL, 'Validé', 'STATUT_RESULTAT', 3, 0, 0, 0, 2);

/* FRONT menu principal */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (153, NULL, 3, 'Autodiagnostics', 'hopitalnumerique_autodiag_front_outil_index', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 8);