INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`) VALUES (NULL, NULL, 'Liste déroulante', 'TYPE_QUESTION', '3', '0', '0', '1', '1');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`) VALUES (NULL, NULL, 'Bouton Radio', 'TYPE_QUESTION', '3', '0', '0', '1', '2');
INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`) VALUES (NULL, NULL, 'Champ Texte', 'TYPE_QUESTION', '3', '0', '0', '1', '3');

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(147, NULL, 1, 'Gestion des autodiag', 'hopitalnumerique_autodiag_outil', '[]', NULL, NULL, 'fa fa-bar-chart-o', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 9);
