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

---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Update objets -> publication */
