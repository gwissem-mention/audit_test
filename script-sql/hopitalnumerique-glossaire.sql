INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (38, 'FrontOffice - Glossaire', '/^\\/glossaire/', 37, 2);

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (39, 'BackOffice - Gestion du glossaire', '/^\\/admin\\/glossaire/', 28, 1);


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (181, NULL, 2, 'Glossaire', 'hopitalnumerique_glossaire', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 5);
