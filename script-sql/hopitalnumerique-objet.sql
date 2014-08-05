UPDATE hn_objet SET obj_glossaires = "a:0:{}";
UPDATE hn_objet_contenu SET con_glossaires = "a:0:{}";
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (179, 54, 1, 'Glossaire', 'hopitalnumerique_glossaire_glossaire', '[]', NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4);
