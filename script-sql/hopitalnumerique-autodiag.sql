/* Menu Admin */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(147, NULL, 1, 'Gestion des autodiag', 'hopitalnumerique_autodiag_outil', '[]', NULL, NULL, 'fa fa-bar-chart-o', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 9);

/* Items de menu pour les autodiag */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 68, 3, 'Autodiagnostics', 'hopitalnumerique_autodiag_front_comptehn', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);

/* FRONT menu principal */
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (153, NULL, 3, 'Autodiagnostics', 'hopitalnumerique_autodiag_front_outil_index', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 8);
