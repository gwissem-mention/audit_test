INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, NULL, 1, 'Gestion des signalements de bug', 'hopitalnumerique_report_admin_report', '[]', NULL, NULL, 'fa glyphicon-arrow-up', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 8);