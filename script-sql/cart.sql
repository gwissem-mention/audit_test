INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_cart_report_form_description', '<p>Lorem ipsum dolor sit amet consectetur</p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_cart_report_form_description' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_cart_report_description', '<p>Lorem ipsum dolor sit amet consectetur</p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_cart_report_description' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_cart_description', '<p>Lorem ipsum dolor sit amet consectetur</p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_cart_description' LIMIT 1), dom_id FROM hn_domaine;
