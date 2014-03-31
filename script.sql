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

ALTER TABLE `hn_intervention_demande` DROP FOREIGN KEY `FK_6DEA57304F81EDEA` ;
ALTER TABLE `hn_intervention_regroupement` DROP FOREIGN KEY `FK_39B795AD7DDE15` ;
ALTER TABLE `hn_intervention_regroupement` DROP FOREIGN KEY `FK_39B795B56A61A6` ;
ALTER TABLE `hn_intervention_regroupement` DROP FOREIGN KEY `FK_39B7952B07AECF` ;
ALTER TABLE `hn_intervention_etablissement_rattache` DROP FOREIGN KEY `FK_DA1B4928C7F0ABC4` ;
ALTER TABLE `hn_intervention_ambassadeur_historique` DROP FOREIGN KEY `FK_1D720677C7F0ABC4` ;
ALTER TABLE `hn_intervention_evaluation` DROP FOREIGN KEY `FK_64C51B55C7F0ABC4` ;
ALTER TABLE `hn_intervention_objet` DROP FOREIGN KEY `FK_7845C1B3C7F0ABC4` ;

INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body` ,
`mail_params`
)
VALUES (
'27', '[HOPITALNUMERIQUE] - Demande d''intervention / En attente CMSI / Relance simple CMSI', 'Demande d''intervention / En attente CMSI / Relance simple CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention est en attente CMSI : %l Cordialement,', '{"%u":"Nom d''utilisateur","%l":"Lien vers la demande"}'
);

---------------------------------------------------------------------------------------------
/* QSO - 24/03/2014
   PROD -> DEV
   Update objets -> publication */
