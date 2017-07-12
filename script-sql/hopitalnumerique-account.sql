INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_moncompte_competences_presentation', '<p>Lorem ipsum dolor sit amet consectetur</p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_moncompte_competences_presentation' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_moncompte_competences_joindre_cdp', '<p>Lorem ipsum dolor sit amet consectetur</p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_moncompte_competences_joindre_cdp' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_moncompte_competences_hobbies', '<p>Lorem ipsum dolor sit amet consectetur</p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_moncompte_competences_hobbies' LIMIT 1), dom_id FROM hn_domaine;
