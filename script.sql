/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* GME - 13/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* RLE - 28/02/2014
   PROD -> DEV
   Lorem ipsum */


---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Lorem ipsum */
INSERT INTO `core_faq_categorie` (`cat_id`, `cat_name`, `cat_icon`) VALUES (1, 'Général', NULL);
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(109, 54, 1, 'Gestion de la FAQ', 'nodevo_faq_faq', '[]', NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4),
(110, 109, 1, 'Ajouter une FAQ', 'nodevo_faq_faq_add', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(111, 109, 1, 'Afficher un élément de la FAQ', 'nodevo_faq_faq_show', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2),
(112, 109, 1, 'Editer un élément de la FAQ', 'nodevo_faq_faq_edit', '[]', NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3);
