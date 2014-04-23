/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* GME - 04/04/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* RLE - 28/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* QSO - 22/04/2014
   DEV -> PROD
 */
UPDATE `hn_etablissement` SET `eta_codepostal` = CONCAT('0',`eta_codepostal`) WHERE length(`eta_codepostal`) = 4;
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (125, NULL, 1, 'Gestion des paiements', 'hopitalnumerique_paiement_facture', NULL, 0, NULL, 'fa fa-credit-card', 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 7),
    (126, 125, 1, 'Configuration des règles de remboursement', 'hopitalnumerique_paiement_config', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1);

INSERT INTO `hn_facture_remboursement` (`rem_id`, `ref_region`, `rem_intervention`, `rem_supplement`, `rem_repas`, `rem_gestion`)
VALUES
    (1, 40, 0, 0, 0, 0),
    (2, 41, 22, 0, 22, 0),
    (3, 42, 0, 0, 0, 0),
    (4, 43, 0, 0, 0, 0),
    (5, 44, 0, 0, 0, 0),
    (6, 45, 0, 0, 0, 0),
    (7, 46, 0, 0, 0, 0),
    (8, 47, 0, 0, 0, 0),
    (9, 48, 0, 0, 0, 0),
    (10, 49, 0, 0, 0, 0),
    (11, 50, 0, 0, 0, 0),
    (12, 51, 0, 0, 0, 0),
    (13, 52, 0, 0, 0, 0),
    (14, 53, 0, 0, 0, 0),
    (15, 54, 0, 0, 0, 0),
    (16, 55, 0, 0, 0, 0),
    (17, 56, 0, 0, 0, 0),
    (18, 57, 0, 0, 0, 0),
    (19, 58, 0, 0, 0, 0),
    (20, 59, 0, 0, 0, 0),
    (21, 60, 0, 0, 0, 0),
    (22, 61, 0, 0, 0, 0),
    (23, 62, 0, 0, 0, 0),
    (24, 63, 0, 0, 0, 0),
    (25, 64, 0, 0, 0, 0),
    (26, 65, 0, 0, 0, 0);
