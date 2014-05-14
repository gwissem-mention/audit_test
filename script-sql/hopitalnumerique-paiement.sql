INSERT INTO `hn_facture_remboursement` (`rem_id`, `ref_region`, `rem_intervention`, `rem_supplement`, `rem_repas`, `rem_gestion`)
VALUES
    (1, 40, 70, 0, 20, 15),
    (2, 41, 70, 0, 20, 15),
    (3, 42, 70, 0, 20, 15),
    (4, 43, 45, 0, 20, 15),
    (5, 44, 45, 0, 20, 15),
    (6, 45, 70, 0, 20, 15),
    (7, 46, 45, 0, 20, 15),
    (8, 47, 70, 0, 20, 15),
    (9, 48, 45, 0, 20, 15),
    (10, 49, 70, 0, 20, 15),
    (11, 50, 45, 0, 20, 15),
    (12, 51, 45, 0, 20, 15),
    (13, 52, 70, 0, 20, 15),
    (14, 53, 15, 0, 20, 15),
    (15, 54, 100, 0, 20, 15),
    (16, 55, 100, 0, 20, 15),
    (17, 56, 100, 0, 20, 15),
    (18, 57, 100, 0, 20, 15),
    (19, 58, 70, 0, 20, 15),
    (20, 59, 70, 0, 20, 15),
    (21, 60, 45, 0, 20, 15),
    (22, 61, 70, 0, 20, 15),
    (23, 62, 70, 0, 20, 15),
    (24, 63, 70, 0, 20, 15),
    (25, 64, 70, 0, 20, 15),
    (26, 65, 45, 0, 20, 15);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (125, NULL, 1, 'Gestion des paiements', 'hopitalnumerique_paiement_facture', NULL, 0, NULL, 'fa fa-credit-card', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
    (126, 125, 1, 'Configuration des règles de remboursement', 'hopitalnumerique_paiement_config', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
    (127, NULL, 4, 'Suivi des paiements', 'hopitalnumerique_paiement_front', NULL, NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (27, 'BackOffice - Gestion des Paiements', '/^\\/admin\\/facture/', 27, 1),
    (28, 'FrontOffice - Tableau de bord : Suivi des paiements', '/^\\/compte-hn\\/suivi-des-paiements/', 20, 2);

UPDATE `hn_reference` SET `ref_libelle`= 'Facture à émettre' WHERE `ref_id` = 5;
UPDATE `hn_reference` SET `ref_libelle`= 'Facture émise' WHERE `ref_id` = 6;

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (142, 68, 3, 'Suivi des paiements', 'hopitalnumerique_paiement_front', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4),
    (143, 68, 3, 'Modules Thématiques', 'hopitalnumerique_module_inscription_index_front', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);
