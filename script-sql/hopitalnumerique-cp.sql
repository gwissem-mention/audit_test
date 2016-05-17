# 4548 Rechercher / Remplacer "communauté de pratiques" => "communauté de pratique"
# Il faut enregistrer un item de menu du menu principal pour vider le cache
UPDATE core_menu_item SET itm_name = 'Communauté de pratique' WHERE itm_id = 253 AND itm_route = 'hopitalnumerique_communautepratique_admin_groupe_list';
UPDATE core_menu_item SET itm_name = 'La communauté de pratique' WHERE itm_id = 256 AND itm_route = 'hopital_numerique_publication_publication_article';
UPDATE core_ressource SET res_nom = 'FrontOffice - Communauté de pratique' WHERE res_id = 62 AND res_nom = 'FrontOffice - Communauté de pratiques';
