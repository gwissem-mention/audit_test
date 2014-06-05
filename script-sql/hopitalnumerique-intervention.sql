INSERT INTO `hn_intervention_initiateur` (
`intervinit_id` ,
`intervinit_type`
)
VALUES (
'3', 'ANAP'
);

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
NULL , '114', '1', 'Créer des interventions', 'hopital_numerique_intervention_admin_demande_nouveau', NULL , '0', NULL , NULL , '0', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '1'
);
