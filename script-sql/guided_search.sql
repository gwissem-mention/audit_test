INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_guidedSearch_entryPoint_title', 'Parcourir les étapes-projets');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_guidedSearch_entryPoint_title' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_guidedSearch_entryPoint_description', '            <p>
                Vous comptez vous lancer dans un projet numérique ? Vous souhaitez anticiper les questions à se poser ou les difficultés à venir et découvrir ce que l''ANAP propose pour les éviter ?
            </p>
            <p>
                Le parcours guidé vous restitue les questions fréquentes à chaque étape de la vie de votre projet et vous présente les productions de l''ANAP qui pourront vous aider à mieux maîtriser les risques projets qui pourront survenir.
            </p>
            <p>
                Laissez-vous guider !
            </p>
');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_guidedSearch_entryPoint_description' LIMIT 1), dom_id FROM hn_domaine;


INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_guidedSearch_entryPoint_highlight', '<p>
                                    Etape par étape, j''identifie les risques à venir et les solutions pour les maîtriser.
                                </p>');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_guidedSearch_entryPoint_highlight' LIMIT 1), dom_id FROM hn_domaine;


INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_guidedSearch_risk_probability', 'Probabilité');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_guidedSearch_risk_probability' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_guidedSearch_risk_impact', 'Impact');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_guidedSearch_risk_impact' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUE ('Module_guidedSearch_risk_criticity', 'Criticité');
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_guidedSearch_risk_criticity' LIMIT 1), dom_id FROM hn_domaine;

INSERT INTO core_mail (mail_id, mail_objet, mail_description, mail_expediteur_mail, mail_expediteur_name, mail_body, mail_params, mail_notification_region_referent) VALUES (74, 'Recherche guidée', 'Recherche guidée - Synthèse', '', '', 'Bonjour

Voici une recherche guidée qui pourrait vous intéresser.

Cordialement', '', 0);
