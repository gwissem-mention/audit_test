INSERT INTO hn.core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (null, 1, 'Autodiagnotics', 'hopitalnumerique_autodiag_list', null, 0, null, 'fa fa-briefcase', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);
SET @menu_id = LAST_INSERT_ID();

INSERT INTO hn.core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@menu_id, 1, 'Ajouter un autodiagnostic', 'hopitalnumerique_autodiag_create', null, 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO hn.core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@menu_id, 1, 'Editer un autodiagnostic', null, null, 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);
SET @edit_id = LAST_INSERT_ID();

INSERT INTO hn.core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Propriétés générales', 'hopitalnumerique_autodiag_edit', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO hn.core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Questionnaire', 'hopitalnumerique_autodiag_edit_survey', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO hn.core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Algorithme', 'hopitalnumerique_autodiag_edit_algorithm', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO hn.core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_uri, itm_icon, itm_display, itm_display_children, itm_role, itm_order)
VALUES (@edit_id, 1, 'Restitution', 'hopitalnumerique_autodiag_edit_restitution', '{"id":"0"}', 0, null, null, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);
