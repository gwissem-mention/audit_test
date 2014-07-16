INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(NULL, NULL, 1, 'Gestion de l\'aide à l\'expression du besoin', 'hopital_numerique_expbesoin_index', '[]', NULL, NULL, 'fa fa-bug', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 66, 3, 'Recherche JS', NULL, '[]', NULL, 'javascript:rechercheAideEtBesoin();', NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);
