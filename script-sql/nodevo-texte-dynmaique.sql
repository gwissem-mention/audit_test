INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(240, 16, 1, 'Texte dynamique', 'nodevo_textedynamique_code', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(NULL, 240, 1, 'Texte dynamique - add', 'nodevo_textedynamique_code_add', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
	(NULL, 240, 1, 'Texte dynamique - edit', 'nodevo_textedynamique_code_edit', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2);

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
	(50, 'BackOffice - Gestion des domaines', '/^\\/admin\\/domaine/', 15, 2);
