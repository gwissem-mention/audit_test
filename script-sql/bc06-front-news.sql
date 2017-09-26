INSERT INTO core_menu_item (itm_parent, mnu_menu, itm_name, itm_route, itm_display_children, itm_role, itm_order, itm_display) SELECT itm_id, 3, 'Actualité', 'hopitalnumerique_communautepratique_news_index', 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1, 1 FROM core_menu_item WHERE itm_route = 'hopitalnumerique_communautepratique_accueil_index' AND itm_parent IS NULL
UPDATE core_menu_item SET itm_name = 'Espace de discussion', itm_route_parameters = NULL, itm_route = 'hopitalnumerique_communautepratique_discussions_public' WHERE itm_route = 'ccdn_forum_user_category_index' AND itm_name LIKE '%espace%' AND itm_parent = 273;
UPDATE core_menu_item SET itm_route = NULL, itm_route_parameters = NULL WHERE itm_route = 'hopitalnumerique_communautepratique_accueil_index' AND itm_parent IS NULL;

