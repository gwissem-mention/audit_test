INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_contextualNavigation_lostPage_description', '<p>Le centre de ressources de l''ANAP offre différents services: recherche, parcours guidé, forum de discussion, communauté de pratique, autodiagnostics.</p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_contextualNavigation_lostPage_description' LIMIT 1), dom_id FROM hn_domaine;


INSERT INTO `core_ressource` (`res_nom`, `res_pattern`, `res_order`, `res_type`) VALUES ('FrontOffice - Vous etes perdu', '/^\\/(.*)\\/(.*)\\/vous-etes-perdu/', '1', '2');


INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('1', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('2', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('3', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('4', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('5', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('6', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('7', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('8', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('9', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('11', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('100', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('101', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('103', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('105', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('106', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('107', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('108', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('109', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
INSERT INTO `core_acl` (`ro_id`, `res_id`, `acl_read`, `acl_write`) VALUES ('110', (SELECT res_id FROM core_ressource WHERE res_nom = 'FrontOffice - Vous etes perdu'), '1', '1');
