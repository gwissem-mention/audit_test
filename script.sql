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

INSERT INTO `core_ressource` (
`res_id` ,
`res_nom` ,
`res_pattern` ,
`res_order` ,
`res_type`
)
VALUES (
'23', 'BackOffice - Gestion des interventions', '/^\\/admin\\/intervention/', '23', '1'
);
UPDATE `core_menu_item` SET `itm_name` = 'Éditer une intervention' WHERE `core_menu_item`.`itm_id` =105;
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
NULL , '104', '1', 'Voir une intervention', 'hopital_numerique_intervention_admin_demande_voir', NULL , '0', NULL , NULL , '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '3'
);

---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Update objets -> publication */
