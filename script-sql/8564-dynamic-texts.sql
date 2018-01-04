INSERT INTO core_textedynamique_code (txt_code, txt_texte) VALUES
  ('Module_intervention_nouveau_description', '<p>Remplissez le formulaire ci-après si vous souhaitez solliciter l''intervention d''un ambassadeur dans votre établissement. Après l''acceptation de votre demande par le CMSI et l''ambassadeur de votre région, ce dernier vous contactera afin d''organiser son intervention. Une fois l''intervention réalisée, vous serez sollicités par mail pour l''évaluer.</p>'),
  ('Module_intervention_nouveau_description_cmsi', '<p>Remplissez le formulaire ci-après si vous souhaitez solliciter l''intervention d''un ambassadeur dans votre établissement. Après l''acceptation de votre demande par l''ambassadeur de votre région, ce dernier contactera le ou les établissements pour planifier son intervention. Un mail vous parviendra lorsque l''évaluation de l''intervention sera disponible sur la plateforme.</p>')
;

INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_intervention_nouveau_description'), dom_id FROM hn_domaine;
INSERT INTO core_textedynamique_code_domaine (txt_id, dom_id) SELECT (SELECT txt_id FROM core_textedynamique_code WHERE txt_code = 'Module_intervention_nouveau_description_cmsi'), dom_id FROM hn_domaine;
