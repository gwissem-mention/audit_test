INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (null, 1, 'Autodiagnotics', 'hopitalnumerique_autodiag_list', null, 0, null, 'fa fa-briefcase', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);
SET @menu_id = LAST_INSERT_ID();

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@menu_id, 1, 'Ajouter un autodiagnostic', 'hopitalnumerique_autodiag_create', null, 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@menu_id, 1, 'Editer un autodiagnostic', null, null, 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);
SET @edit_id = LAST_INSERT_ID();

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Propriétés générales', 'hopitalnumerique_autodiag_edit', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Questionnaire', 'hopitalnumerique_autodiag_edit_survey', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Algorithme', 'hopitalnumerique_autodiag_edit_algorithm', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Restitution', 'hopitalnumerique_autodiag_edit_restitution', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Résultats', 'hopitalnumerique_autodiag_edit_entries', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Afficher', 'hopitalnumerique_autodiag_edit_result_show_entry', '{"entry":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

-- ACL Ressource
INSERT INTO core_ressource (res_nom, res_pattern, res_order, res_type)
VALUES ('BackOffice - Autodiagnostics', '/^\\/admin\\/autodiag/', 1, 1);

-- Ajouter le menu Mes autodiagnostics dans Mon compte FO
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES (336, 68, 3, 'Mes autodiagnostics', 'hopitalnumerique_autodiag_account_index', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4);

/* 11:43:38 Nodevo05 preprod_monhopitalnumeriqueautodiag */ DELETE FROM `core_menu_item` WHERE `itm_id` IN ('197','306');

/* 12:01:21 Nodevo05 preprod_monhopitalnumeriqueautodiag */ DELETE FROM `core_menu_item` WHERE `itm_id` IN ('71','216','246','272');

/* 09:42:57 Nodevo05 preprod_monhopitalnumeriqueautodiag */ DELETE FROM core_acl where res_id IN (42,43);
/* 09:43:26 Nodevo05 preprod_monhopitalnumeriqueautodiag */ DELETE FROM `core_ressource` WHERE `res_id` IN ('42','43');
