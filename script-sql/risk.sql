UPDATE core_menu_item SET itm_order = itm_order + 1 WHERE itm_parent = 16 AND itm_order > 1;

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_display, itm_display_children, itm_role, itm_order) VALUE
  (16, 1, 'Risques', 'hopitalnumerique_objet_risk_list', '[]', 0, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2)
;

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_display, itm_display_children, itm_role, itm_order) VALUE
 ((SELECT cmi.itm_id FROM core_menu_item as cmi WHERE cmi.itm_route = 'hopitalnumerique_objet_risk_list'), 1, 'Editer un risque', 'hopitalnumerique_objet_risk_edit', '[]', 0, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2)
;

INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_route_parameters, itm_route_absolute, itm_display, itm_display_children, itm_role, itm_order) VALUE
 ((SELECT cmi.itm_id FROM core_menu_item as cmi WHERE cmi.itm_route = 'hopitalnumerique_objet_risk_list'), 1, 'Ajouter un risque', 'hopitalnumerique_objet_risk_add', '[]', 0, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2)
;


INSERT INTO core_ressource (res_nom, res_pattern, res_order, res_type) VALUE
  ('Backoffice - Risques', '/^\\/admin\\/risques/', 1, 2);
