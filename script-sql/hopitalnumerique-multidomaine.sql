INSERT INTO `core_menu` (`mnu_id`, `mnu_name`, `mnu_alias`, `mnu_cssClass`, `mnu_cssId`, `mnu_lock`)
VALUES
	(7, 'Front - Générique', 'menu-main-front_gen', 'menu-main', 'menu', 1);

INSERT INTO `hn_domaine_template` (`temp_id`, `temp_nom`)
VALUES
	(4, 'Template générique');

/* 15:31:45 HN */ UPDATE `hn_domaine_template` SET `temp_nom` = 'Template Macrodiag' WHERE `temp_id` = '1';


/* 16:37:02 HN */ UPDATE `core_menu_item` SET `itm_route_parameters` = NULL WHERE `itm_id` = '215';
/* 16:39:49 HN */ UPDATE `core_menu` SET `mnu_alias` = 'menu-main-front_2' WHERE `mnu_id` = '3';
/* 16:39:55 HN */ UPDATE `core_menu` SET `mnu_alias` = 'menu-main-front_1' WHERE `mnu_id` = '4';
