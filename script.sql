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

INSERT INTO `core_menu_item` (
`itm_id` ,
`itm_parent` ,
`mnu_menu` ,
`itm_name` ,
`itm_route` ,
`itm_route_parameters` ,
`itm_route_absolute` ,
`itm_uri` ,
`itm_icon` ,
`itm_display` ,
`itm_display_children` ,
`itm_role` ,
`itm_order`
)
VALUES (
NULL , '54', '1', 'Gestion des interventions', 'hopital_numerique_intervention_admin_liste', NULL , '0', NULL , NULL , '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '3'
);


---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Update objets -> publication */
INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES (NULL, 'FrontOffice - Actualités', '/^\\/actualites', '21', '2');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('10', '13', '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('10', '21', '1', '1');
UPDATE `core_menu_item` SET `itm_route` = NULL, `itm_route_parameters` = NULL WHERE `core_menu_item`.`itm_id` = 60;
INSERT INTO `core_menu` (`mnu_id`, `mnu_name`, `mnu_alias`, `mnu_cssClass`, `mnu_cssId`, `mnu_lock`) VALUES (5, 'Sous-menu ''HopitalNumérique''', 'menu-sub-front', NULL, 'sous-menu', 1);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(96, NULL, 5, 'Présentation HN', 'hopital_numerique_publication_publication_article', '{"categorie":"article","id":"1","alias":"article-front"}', NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(97, NULL, 5, 'Gouvernance', 'hopital_numerique_publication_publication_article', '{"categorie":"article","id":"1","alias":"article-front"}', NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2),
(98, NULL, 5, 'Projets', 'hopital_numerique_publication_publication_article', '{"categorie":"article","id":"1","alias":"article-front"}', NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 3),
(99, NULL, 5, 'Equipe', 'hopital_numerique_publication_publication_article', '{"categorie":"article","id":"1","alias":"article-front"}', NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(102, 65, 3, 'Actualites détail', 'hopital_numerique_publication_actualite_categorie', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(103, 65, 3, 'Actualites', 'hopital_numerique_publication_actualite', NULL, NULL, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2);