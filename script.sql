/* TDA - 13/02/2014
   PROD -> DEV
   Lorem ipsum */
INSERT .....

/* GME - 19/02/14
   DEv -> PROD
   Ajout des amb/expert + prod maitrisé dans le menu
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(57, 9, 1, 'Formulaire ambassadeur', 'hopitalnumerique_user_ambassadeur_edit', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 2),
(58, 9, 1, 'Formulaire expert', 'hopitalnumerique_user_expert_edit', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
(59, 9, 1, 'Production maitrisée', 'hopitalnumerique_user_ambassadeur_objets', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 0);*/

/* GME - 20/02/14
   DEV -> PROD
   Ajout question DPI questionnaire expert*/
/*INSERT INTO `hn_questionnaire_question` (`que_id`, `qst_id`, `typ_question`, `que_libelle`, `que_obligatoire`, `que_verifJS`, `que_ordre`, `que_alias`, `que_reference_param_tri`) VALUES
(NULL, 1, 3, 'Joindre votre DPI', 1, NULL, 16, 'dpi', NULL);*/

/* QSO - 25/02/14
   DEV -> PROD
   Ajout lien de menu*/
/*INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES
(55, 16, 1, 'Etablissements  non référencés', 'hopitalnumerique_etablissement_autres', NULL, 0, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 4);*/

/* RLE - 25/02/2014
   DEV -> PROD
   Modif URL gestion des médias */
/*UPDATE `core_menu_item` SET `itm_route` = 'nodevo_gestionnaire_media_index' WHERE `core_menu_item`.`itm_id` =44;*/

/* GME - 07/03/2014
   DEV -> PROD
   Ajout lien de menu*/
INSERT INTO `wwwhopitalnumeriquecom`.`core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, NULL, '4', 'Candidature pour devenir ambassadeur', 'hopitalnumerique_user_ambassadeur_front_edit', NULL, NULL, NULL, NULL, '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '8');
INSERT INTO `wwwhopitalnumeriquecom`.`core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`) VALUES (NULL, NULL, '4', 'Candidature pour devenir expert', 'hopitalnumerique_user_expert_front_edit', NULL, NULL, NULL, NULL, '1', '0', 'IS_AUTHENTICATED_ANONYMOUSLY', '9');

/* GME - 10/03/2014
   DEV -> PROD
   Modifs questionnaire*/
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '6' WHERE `hn_questionnaire_question`.`que_id` =16;
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '3' WHERE `hn_questionnaire_question`.`que_id` =17;
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '4' WHERE `hn_questionnaire_question`.`que_id` =18;
UPDATE `wwwhopitalnumeriquecom`.`hn_questionnaire_question` SET `que_ordre` = '5' WHERE `hn_questionnaire_question`.`que_id` =19;

/* RLE - 07/03/2014
   DEV -> PROD
   Interventions*/
CREATE  TABLE IF NOT EXISTS `hn_intervention_initiateur` (
  `intervinit_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID de l\'initiateur de l\'intervention' ,
  `intervinit_type` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Type de l\'initiateur de la demande d\'intervention' ,
  PRIMARY KEY (`intervinit_id`) )
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `hn_intervention_demande` (
  `interv_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Id de l\'intervention' ,
  `referent_id` INT(11) NOT NULL COMMENT 'Utilisateur lié à l\'établissement concerné' ,
  `ambassadeur_id` INT(11) NOT NULL COMMENT 'Ambassadeur qui va effectuer l\'intervention' ,
  `cmsi_id` INT(11) NOT NULL COMMENT 'Le CMSI qui accepte ou pas la demande d\'intervention' ,
  `directeur_id` INT(11) NOT NULL COMMENT 'Le directeur de l\'ES concerné' ,
  `ref_intervention_type_id` INT(11) NOT NULL COMMENT 'Type d\'intervention' ,
  `ref_intervention_etat_id` INT(11) NOT NULL COMMENT 'État actuel de l\'intervention' ,
  `ref_evaluation_etat_id` INT(11) NULL COMMENT 'État de l\'évaluation' ,
  `ref_remboursement_etat_id` INT(11) NULL COMMENT 'État du remboursement de l\'ambassadeur' ,
  `intervinit_id` TINYINT UNSIGNED NOT NULL COMMENT 'Initiateur de l\'intervention' ,
  `interv_date_creation` DATETIME NOT NULL COMMENT 'Date de création de la demande' ,
  `interv_cmsi_date_choix` DATETIME NULL COMMENT 'Date de refus ou de validation du CMSI' ,
  `interv_ambassadeur_date_choix` DATETIME NULL COMMENT 'Date de refus ou de validation de l\'ambassadeur' ,
  `interv_autres_etablissements` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT 'Autres établissements rattachés mais non présents en base' ,
  `interv_description` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT 'Description succinte de l\'intervention' ,
  `interv_difficulte_description` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT 'Description de la difficulté' ,
  `interv_champ_libre` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT 'Champ libre' ,
  `interv_rdv_informations` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT 'Informations pour la prise de RDV' ,
  `interv_refus_message` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT 'Commentaire de l\'éventuel refus de l\'intervention' ,
  PRIMARY KEY (`interv_id`) ,
  INDEX `fk_hn_intervention_core_user` (`referent_id` ASC) ,
  INDEX `fk_hn_intervention_demande_core_user1` (`ambassadeur_id` ASC) ,
  INDEX `fk_hn_intervention_demande_core_user2` (`cmsi_id` ASC) ,
  INDEX `fk_hn_intervention_demande_core_user3` (`directeur_id` ASC) ,
  INDEX `fk_hn_intervention_demande_hn_reference1` (`ref_intervention_type_id` ASC) ,
  INDEX `fk_hn_intervention_demande_hn_reference2` (`ref_intervention_etat_id` ASC) ,
  INDEX `fk_hn_intervention_demande_hn_intervention_initiateur1` (`intervinit_id` ASC) ,
  INDEX `fk_hn_intervention_demande_hn_reference3` (`ref_evaluation_etat_id` ASC) ,
  INDEX `fk_hn_intervention_demande_hn_reference4` (`ref_remboursement_etat_id` ASC) ,
  CONSTRAINT `fk_hn_intervention_core_user`
    FOREIGN KEY (`referent_id` )
    REFERENCES `core_user` (`usr_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_core_user1`
    FOREIGN KEY (`ambassadeur_id` )
    REFERENCES `core_user` (`usr_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_core_user2`
    FOREIGN KEY (`cmsi_id` )
    REFERENCES `core_user` (`usr_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_core_user3`
    FOREIGN KEY (`directeur_id` )
    REFERENCES `core_user` (`usr_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_hn_reference1`
    FOREIGN KEY (`ref_intervention_type_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_hn_reference2`
    FOREIGN KEY (`ref_intervention_etat_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_hn_intervention_initiateur1`
    FOREIGN KEY (`intervinit_id` )
    REFERENCES `hn_intervention_initiateur` (`intervinit_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_hn_reference3`
    FOREIGN KEY (`ref_evaluation_etat_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_demande_hn_reference4`
    FOREIGN KEY (`ref_remboursement_etat_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Intervention d\'un ambassadeur dans un ES';


CREATE  TABLE IF NOT EXISTS `hn_intervention_etablissement_rattache` (
  `interv_id` INT UNSIGNED NOT NULL COMMENT 'Intervention' ,
  `eta_id` INT(11) NOT NULL COMMENT 'Établissement rattaché' ,
  PRIMARY KEY (`interv_id`, `eta_id`) ,
  INDEX `fk_hn_intervention_has_hn_etablissement_hn_etablissement1` (`eta_id` ASC) ,
  INDEX `fk_hn_intervention_has_hn_etablissement_hn_intervention1` (`interv_id` ASC) ,
  CONSTRAINT `fk_hn_intervention_has_hn_etablissement_hn_intervention1`
    FOREIGN KEY (`interv_id` )
    REFERENCES `hn_intervention_demande` (`interv_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_has_hn_etablissement_hn_etablissement1`
    FOREIGN KEY (`eta_id` )
    REFERENCES `hn_etablissement` (`eta_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Liste des établissements qui ont été regroupé avec l\'établis' /* comment truncated */;


CREATE  TABLE IF NOT EXISTS `hn_intervention_objet` (
  `interv_id` INT UNSIGNED NOT NULL COMMENT 'Intervention' ,
  `obj_id` INT(11) NOT NULL COMMENT 'Objet (production ANAP) sollicité pour cette intervention' ,
  PRIMARY KEY (`interv_id`, `obj_id`) ,
  INDEX `fk_hn_intervention_has_hn_objet_hn_objet1` (`obj_id` ASC) ,
  INDEX `fk_hn_intervention_has_hn_objet_hn_intervention1` (`interv_id` ASC) ,
  CONSTRAINT `fk_hn_intervention_has_hn_objet_hn_intervention1`
    FOREIGN KEY (`interv_id` )
    REFERENCES `hn_intervention_demande` (`interv_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_has_hn_objet_hn_objet1`
    FOREIGN KEY (`obj_id` )
    REFERENCES `hn_objet` (`obj_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `hn_intervention_regroupement_type` (
  `intervregtyp_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID du type de regroupement' ,
  `intervregtyp_libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Libellé du regroupement' ,
  PRIMARY KEY (`intervregtyp_id`) )
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `hn_intervention_regroupement` (
  `interv_principale_id` INT UNSIGNED NOT NULL COMMENT 'Intervention sur laquelle d\'autres interventions ont été rattachées' ,
  `interv_regroupee_id` INT UNSIGNED NOT NULL COMMENT 'Intervention qui a été rattachée' ,
  `intervregtyp_id` TINYINT UNSIGNED NOT NULL COMMENT 'Type de regroupement' ,
  PRIMARY KEY (`interv_principale_id`, `interv_regroupee_id`) ,
  INDEX `fk_hn_intervention_has_hn_intervention_hn_intervention2` (`interv_regroupee_id` ASC) ,
  INDEX `fk_hn_intervention_has_hn_intervention_hn_intervention1` (`interv_principale_id` ASC) ,
  INDEX `fk_hn_intervention_regroupement_hn_intervention_regroupement_1` (`intervregtyp_id` ASC) ,
  CONSTRAINT `fk_hn_intervention_has_hn_intervention_hn_intervention1`
    FOREIGN KEY (`interv_principale_id` )
    REFERENCES `hn_intervention_demande` (`interv_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_has_hn_intervention_hn_intervention2`
    FOREIGN KEY (`interv_regroupee_id` )
    REFERENCES `hn_intervention_demande` (`interv_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_regroupement_hn_intervention_regroupement_1`
    FOREIGN KEY (`intervregtyp_id` )
    REFERENCES `hn_intervention_regroupement_type` (`intervregtyp_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Liste des interventions qui ont été regroupées';


CREATE  TABLE IF NOT EXISTS `hn_intervention_ambassadeur_historique` (
  `interv_id` INT UNSIGNED NOT NULL COMMENT 'Intervention concernée' ,
  `ambassadeur_ancien_id` INT(11) NOT NULL COMMENT 'Ambassadeur étant anciennement sollicité pour cette intervention' ,
  PRIMARY KEY (`interv_id`, `ambassadeur_ancien_id`) ,
  INDEX `fk_hn_intervention_has_core_user_core_user1` (`ambassadeur_ancien_id` ASC) ,
  INDEX `fk_hn_intervention_has_core_user_hn_intervention1` (`interv_id` ASC) ,
  CONSTRAINT `fk_hn_intervention_has_core_user_hn_intervention1`
    FOREIGN KEY (`interv_id` )
    REFERENCES `hn_intervention_demande` (`interv_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_has_core_user_core_user1`
    FOREIGN KEY (`ambassadeur_ancien_id` )
    REFERENCES `core_user` (`usr_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Historique des ambassadeurs qui ont été demandés pour l\'inte' /* comment truncated */;


CREATE  TABLE IF NOT EXISTS `hn_intervention_evaluation` (
  `interveval_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID de l\'évaluation d\'une intervention' ,
  `interv_id` INT UNSIGNED NOT NULL COMMENT 'Intervention de l\'évaluation' ,
  `interveval_date_creation` DATETIME NOT NULL COMMENT 'Date de création de l\'évalution' ,
  `interveval_presentation_supplement` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT 'Travail effectué en plus par l\'ambassadeur' ,
  `ref_attente_id` INT(11) NOT NULL COMMENT 'Les productions ANAP abordées correspondaient-elles à vos attentes ?' ,
  `ref_presentation_contexte_id` INT(11) NOT NULL COMMENT 'L\'apport de la présentation vous semble-t-il applicable dans votre contexte ?' ,
  `ref_utilite_id` INT(11) NOT NULL COMMENT 'Utilité de l\'intervention' ,
  `ref_utilisation_prealable_id` INT(11) NOT NULL COMMENT 'Utilisation de la palteforme ANAP avant l\'intervention' ,
  `ref_modalites_pratiques_id` INT(11) NOT NULL COMMENT 'Modalités pratiques d\'organisation de l\'intervention satisfaisantes ?' ,
  `interveval_commentaire` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL ,
  PRIMARY KEY (`interveval_id`) ,
  INDEX `fk_hn_intervention_evaluation_hn_intervention_demande1` (`interv_id` ASC) ,
  INDEX `fk_hn_intervention_evaluation_hn_reference1` (`ref_attente_id` ASC) ,
  INDEX `fk_hn_intervention_evaluation_hn_reference2` (`ref_presentation_contexte_id` ASC) ,
  INDEX `fk_hn_intervention_evaluation_hn_reference3` (`ref_utilite_id` ASC) ,
  INDEX `fk_hn_intervention_evaluation_hn_reference4` (`ref_utilisation_prealable_id` ASC) ,
  INDEX `fk_hn_intervention_evaluation_hn_reference5` (`ref_modalites_pratiques_id` ASC) ,
  CONSTRAINT `fk_hn_intervention_evaluation_hn_intervention_demande1`
    FOREIGN KEY (`interv_id` )
    REFERENCES `hn_intervention_demande` (`interv_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_evaluation_hn_reference1`
    FOREIGN KEY (`ref_attente_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_evaluation_hn_reference2`
    FOREIGN KEY (`ref_presentation_contexte_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_evaluation_hn_reference3`
    FOREIGN KEY (`ref_utilite_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_evaluation_hn_reference4`
    FOREIGN KEY (`ref_utilisation_prealable_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hn_intervention_evaluation_hn_reference5`
    FOREIGN KEY (`ref_modalites_pratiques_id` )
    REFERENCES `hn_reference` (`ref_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


ALTER TABLE `hn_intervention_regroupement` ADD FOREIGN KEY ( `interv_principale_id` ) REFERENCES `hn_intervention_demande` (
`interv_id`
) ON DELETE NO ACTION ON UPDATE NO ACTION ;

ALTER TABLE `hn_intervention_regroupement` ADD FOREIGN KEY ( `interv_regroupee_id` ) REFERENCES `hn_intervention_demande` (
`interv_id`
) ON DELETE NO ACTION ON UPDATE NO ACTION ;

ALTER TABLE `hn_intervention_regroupement` ADD FOREIGN KEY ( `intervregtyp_id` ) REFERENCES `hn_intervention_regroupement_type` (
`intervregtyp_id`
) ON DELETE NO ACTION ON UPDATE NO ACTION ;


INSERT INTO `hn_reference` (`ref_id` ,`parent_id` ,`ref_libelle` ,`ref_code` ,`ref_etat` ,`ref_dictionnaire` ,`ref_recherche` ,`ref_lock` ,`ref_order`) VALUES (NULL , NULL , 'Type d''intervention 1', 'TYPE_INTERVENTION', '3', '1', '0', '1', '1');
INSERT INTO `hn_reference` (`ref_id` ,`parent_id` ,`ref_libelle` ,`ref_code` ,`ref_etat` ,`ref_dictionnaire` ,`ref_recherche` ,`ref_lock` ,`ref_order`) VALUES (NULL , NULL , 'Type d''intervention 2', 'TYPE_INTERVENTION', '3', '1', '0', '1', '1');


INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'3', '[HOPITALNUMERIQUE] - Création d''une demande d''intervention', 'Création d''une demande d''intervention', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Votre demande d''intervention a correctement été créée. Cordialement,'
);
INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'4', '[HOPITALNUMERIQUE] - Demande d''intervention', 'Acceptation ou non d''une demande d''intervention par l''ambassadeur', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''intervention a été créée. Vous puvez la valider ou la refuser en visitant : %l Cordialement,'
);
INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'5', '[HOPITALNUMERIQUE] - Demande d''intervention', 'Alerte référent d''une demande d''intervention émise par un CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été émise en votre nom et va être étudiée par l''ambassadeur. Cordialement,'
);
INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'6', '[HOPITALNUMERIQUE] - Demande d''intervention acceptée par le CMSI', 'Demande d''intervention acceptée par le CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été acceptée. Vous pouvez vous rendre à votre interface pour la gérer. Cordialement,'
);
INSERT INTO `core_mail` (
`mail_id` ,
`mail_objet` ,
`mail_description` ,
`mail_expediteur_mail` ,
`mail_expediteur_name` ,
`mail_body`
)
VALUES (
'7', '[HOPITALNUMERIQUE] - Demande d''intervention refusée par le CMSI', 'Demande d''intervention refusée par le CMSI', 'communication@anap.fr', 'ANAP Hôpital numérique', 'Bonjour %u, Une demande d''''intervention a été refusée. %c Cordialement,'
);










