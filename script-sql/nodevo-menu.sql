INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (125, NULL, 1, 'Gestion des paiements', 'hopitalnumerique_paiement_facture', NULL, 0, NULL, 'fa fa-credit-card', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
    (126, 125, 1, 'Configuration des règles de remboursement', 'hopitalnumerique_paiement_config', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);