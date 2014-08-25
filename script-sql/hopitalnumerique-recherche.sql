INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
	(NULL, NULL, 1, 'Gestion de l\'aide à l\'expression du besoin', 'hopital_numerique_expbesoin_index', '[]', NULL, NULL, 'fa fa-bug', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 66, 3, 'Recherche JS', NULL, '[]', NULL, 'javascript:rechercheAideEtBesoin();', NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);


INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 66, 3, 'Recherche par parcours', NULL, '[]', NULL, 'hopital_numerique_recherche_parcours_homepage_front', NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (NULL, 'FrontOffice - Accès à la recherche par parcours', '/^\\/recherche-par-parcours/', 39, 2);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, NULL, 1, 'Recherche par parcours', 'hopital_numerique_recherche_parcours_homepage', '[]', NULL, NULL, 'fa fa-search-plus', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (NULL, 'BackOffice - Gestion de la recherche par parcours', '/^\\admin\/recherche-par-parcours/', 40, 1);


/* Recherche par parcours : id fixe */
INSERT INTO `hn_recherche_recherche_parcours` (`rrp_id`, `ref_id`, `rrp_order`)
VALUES
    (1, 291, 2),
    (2, 292, 3),
    (3, 293, 5),
    (4, 294, 1),
    (5, 295, 4);


/* 22/08 */
UPDATE hn_recherche_maitrise_user SET rmu_prendre_en_compte = FALSE;