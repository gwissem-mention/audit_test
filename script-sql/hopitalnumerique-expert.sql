INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (530, NULL, 'Cycle annuel de collèges d\'experts', 'ACTIVITE_TYPE', 3, 0, 0, 0, 1),
    (531, NULL, 'Groupe de travail', 'ACTIVITE_TYPE', 3, 0, 0, 0, 2),
    (532, NULL, 'Avis d\'experts', 'ACTIVITE_TYPE', 3, 0, 0, 0, 3);


INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (535, NULL, 'Deloite', 'PRESTATAIRE', 3, 0, 0, 0, 1),
    (536, NULL, 'Sanexis', 'PRESTATAIRE', 3, 0, 0, 0, 2),
    (537, NULL, 'Columbus', 'PRESTATAIRE', 3, 0, 0, 0, 3);

INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (540, NULL, 'UO 1 : Support aux experts', 'UO_PRESTATAIRE', 3, 0, 0, 0, 1),
    (541, NULL, 'UO 2 : REX', 'UO_PRESTATAIRE', 3, 0, 0, 0, 2),
    (542, NULL, 'UO 3 : CAPI', 'UO_PRESTATAIRE', 3, 0, 0, 0, 3),
    (543, NULL, 'UO 4 : Guide', 'UO_PRESTATAIRE', 3, 0, 0, 0, 4),
    (544, NULL, 'UO 5 : Démarche', 'UO_PRESTATAIRE', 3, 0, 0, 0, 5),
    (545, NULL, 'UO 6 : Outil', 'UO_PRESTATAIRE', 3, 0, 0, 0, 6),
    (546, NULL, 'UO 7 : Expertise en propre', 'UO_PRESTATAIRE', 3, 0, 0, 0, 7);

INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (550, NULL, 'En cours', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 1),
    (551, NULL, 'Validé', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 2),
    (552, NULL, 'Terminé', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 3),
    (553, NULL, 'Présenté', 'ACTIVITE_EXPERT_ETAT', 3, 0, 0, 0, 4);


INSERT INTO `hn_reference` (`ref_id`, `parent_id`, `ref_libelle`, `ref_code`, `ref_etat`, `ref_dictionnaire`, `ref_recherche`, `ref_lock`, `ref_order`)
VALUES
    (560, NULL, '0', 'MONTANT_VACATION', 3, 0, 0, 0, 1);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 68, 3, 'Suivi de l\'activité', 'hopitalnumerique_expert_front_index', NULL, NULL, NULL, NULL, 1, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 10);

INSERT INTO `core_ressource` (`res_id`, `res_nom`, `res_pattern`, `res_order`, `res_type`)
VALUES
    (NULL, 'FrontOffice - Tableau de bord : Suivi d\'activité', '/^\\/compte-hn\\/suivi-activite/', 20, 2);


#Lien menu BO
INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (220, NULL, 1, 'Activité des experts', 'hopitalnumerique_expert_expert_activite', '[]', NULL, NULL, 'fa fa-briefcase', 1, 1, 'IS_AUTHENTICATED_ANONYMOUSLY', 14);

INSERT INTO `core_menu_item` (`itm_id`, `itm_parent`, `mnu_menu`, `itm_name`, `itm_route`, `itm_route_parameters`, `itm_route_absolute`, `itm_uri`, `itm_icon`, `itm_display`, `itm_display_children`, `itm_role`, `itm_order`)
VALUES
    (NULL, 220, 1, 'Activité des experts - Edition', 'hopitalnumerique_expert_expert_activite', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
    (NULL, 220, 1, 'Evenement des experts', 'hopitalnumerique_expert_evenement_expert', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
    (NULL, 220, 1, 'Evenement des experts - Edition ', 'hopitalnumerique_expert_evenement_expert_edit', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
    (NULL, 220, 1, 'Activité des experts - Ajout', 'hopitalnumerique_expert_expert_activite_add', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1),
    (NULL, 220, 1, 'Evenement des experts - Ajout', 'hopitalnumerique_expert_evenement_expert_add', NULL, 0, NULL, NULL, 0, 0, 'IS_AUTHENTICATED_ANONYMOUSLY', 1)
;

/* 16:19:55 HN */ UPDATE `core_user` SET `usr_biographie` = '<strong>Jean-Paul Bellon a commencé sa carrière comme comptable dans une clinique de la banlieue sud de Paris, A 28 ans on lui confie la direction d’une clinique </strong><br /><br />\r\n\r\nEn <strong>1990</strong> il devient, pendant 6 ans Directeur de la Clinique Girardin à Enghien les Bains, il met en place un nouveau Système Informatique, il instaure un dialogue social.<br /><br />\r\n\r\nEn <strong>1998</strong> à Poissy il a en charge le projet de construction d’un nouvel établissement, pour regrouper deux cliniques.<br /><br />\r\n\r\n<strong>2002</strong>, directeur Général de l’Ile de France d’unités d’auto dialyse gérées par un groupe allemand ;<br /><br />\r\n\r\nEn <strong>2003</strong>, virage vers le secteur privé associatif direction d’EPHAD. <br /><br />\r\n\r\n <strong>2005</strong> Directeur d’un SSR(ESPIC). En 2010 devient expert visiteur auprès de la HAS, pour la certification des établissements de santé du secteur sanitaire.<br /><br />\r\n\r\nEn <strong>2013</strong> il met en place le dossier informatisé du patient (hôpital 2012), par un bing-bang : dossier patient, dossier soins, prescription (circuit du médicament, intégration des CR d’imagerie et des examens de biologie, mise en place d’un PACS et d’un RIS. ' WHERE `usr_id` = '254';
/* 16:21:01 HN */ UPDATE `core_user` SET `usr_biographie` = 'Doctorat en Médecine en 1986, Faculté de Médecine de Brest\r\n<ul>\r\n<li>Médecin Généraliste en cabinet privé de 1987 à 2008</li>\r\n<li>Attaché au CHRU de Brest de 1990 à 2008, responsable de la mise en place des logiciels d’évaluation de l’autonomie des personnes âgées, puis de la prescription nominative en EHPAD</li>\r\n<li>Praticien Hospitalier, référent médical du déploiement de l’informatisation du processus de soin sur l’ensemble du CHRU de Brest depuis 2009</li>\r\n<li>Correspondant local de Logiciovivilance au CHRU de Brest</li>\r\n<li>Diplôme universitaire « Qualité et gestion des risques »</li>\r\n</ul> ' WHERE `usr_id` = '1090';
/* 16:21:17 HN */ UPDATE `core_user` SET `usr_biographie` = 'Diplomée de l’ESSCA d’Angers parcours professionnel dans la presse quotidienne régionale jusqu’en janvier <strong>2011</strong> date à laquelle Sylvie Coiffard a rejoint le groupe Médi-Partenaires en tant que DOSI, devenu Medipôle Partenaires depuis janvier <strong>2015</strong>. ' WHERE `usr_id` = '1114';
/* 16:21:34 HN */ UPDATE `core_user` SET `usr_biographie` = 'Médecin Anesthésiste Réanimateur, Patrick Blanchet exerce la réanimation dans une Clinique de la banlieue Toulousaine et consultant en déploiement et paramétrage pour l\'informatisation en santé, notamment auprès du groupe Capio. Il a participé au GT HAS \"Certification des LAP\" ainsi qu’à l\'European Advisory Panel de Philips Healthcare. ' WHERE `usr_id` = '1325';
/* 16:22:23 HN */ UPDATE `core_user` SET `usr_biographie` = '<strong>Directeur du Système d’information</strong><br/>\r\nDiplômée de l\'EISTI (Ecole Internationale des Sciences du Traitement de l\'Information), Sylvie DELPLANQUE exerce depuis 29 ans dans le secteur de la Santé.<br /><br />\r\nElle débute son parcours professionnel en qualité d’Ingénieur-chef de projet au Centre Hospitalier de Béthune-Beuvry, en 1986, pour conduire l’étude, la réalisation, le suivi et la mise en place d\'un système de communication interservices hospitaliers baptisé Mercure. En 1991, elle intègre le GIE-GLGH (GIE CHR Tours/CH Béthune) pour assurer, outre la direction technique du projet Mercure, des missions d’audits et la commercialisation des produits du groupement jusqu’en 1995. Elle rejoint ensuite l’EPSM des Flandres à Bailleul où elle exerce la fonction de DSIO pendant 4 ans.  <br /><br />\r\nDepuis <strong>2001</strong>, elle dirige le Service Informatique et Téléphonie et des Nouvelles Technologies au Centre Hospitalier de Calais.<br /><br />\r\nRiche d’un savoir-faire éprouvé en assistance technique et méthodologique dans le cadre de procédures d’achat complexes (rédaction du cahier des charges, dépouillement, aide au choix..), organisation et coordination de séminaires et de formations, veille technologique, schéma Directeur de Système d’Information, Sylvie Delplanque apporte son expertise au CNEH depuis 1989. Elle participe à des missions de conseil, d\'assistance à maîtrise d\'ouvrage et à la conception de l’offre de formation SI. <br /><br />\r\nSylvie Delplanque est parallèlement très impliquée dans les projets institutionnels des systèmes d’information. Par exemple, au cours des trois dernières années, elle a été ou est :\r\n<ul>\r\n<li>en <strong>juin 2012</strong> : Leader du segment logiciel dans le cadre du projet ARMEN, vague 1, du  programme PHARE</li>\r\n<li>depuis <strong>janvier 2012</strong>, Administrateur du GCS AMEITIC (Achat Mutualisé d\'Equipements  Informatiques et de Technologies de l\'Information et de la Communication)</li>\r\n<li>depuis <strong>septembre 2013</strong>, Expert Hôpital  Numérique à l’ANAP et Conseiller système d’information et télémédecine à la FHF </li>\r\n</ul> ' WHERE `usr_id` = '1328';
/* 16:22:54 HN */ UPDATE `core_user` SET `usr_biographie` = 'François Meusnier-Delaye a été Directeur Adjoint au CHANGE, Centre Hospitalier Annecy-Genevois, en charge des Systèmes d\'Information.<br /><br />\r\nDepuis <strong>1996</strong>, il s’est investi dans des projets importants tels la fusion des SIH des deux établissements, le pilotage du chantier informatique et courants faibles lié à la construction du nouvel hôpital de la Région d’Annecy ou participer à la mise en production des produits informatiques « santé » en Région Rhône-Alpes. Avant de rejoindre le monde de la santé en <strong>1996</strong>, Il a occupé des postes de DSI dans les secteurs privés banque, assurance et sociétés de services. ' WHERE `usr_id` = '1329';
/* 16:23:09 HN */ UPDATE `core_user` SET `usr_biographie` = 'Praticien en exercice en établissement de santé en Normandie, enseignant notamment au sein de l’Université de Caen, chargé de la gestion de crise d’une centaine d’établissements de santé en France et de leur accompagnement dans la mise en œuvre de démarches de management des risques (groupe de Générale de Santé), Xavier Richomme a participé à l’élaboration de nombreux outils et guides nationaux à l’attention des établissements de santé  sous l’égide de la HAS, de la DGOS ou de l’ANSM. ' WHERE `usr_id` = '1331';
/* 16:23:29 HN */ UPDATE `core_user` SET `usr_biographie` = 'Après un Diplôme d’études supérieures en gestion hospitalière en 1998,  première expérience de cadre supérieur de santé au CHU de Nantes en charge du pôle de gériatrie.<br /><br />\r\nEn <strong>2001</strong> Michelle Daniel entre au Centre Hospitalier Loire Vendéen comme assistante du pôle personnes âgées jusqu’en 2012.<br /><br />\r\nDepuis <strong>2006</strong>, elle est missionnée au sein d la DSIO dans le cadre du déploiement des  prescriptions informatisées et du dossier patient. Elle assure le paramétrage, la formation de l’ensemble des utilisateurs, le déploiement des prescriptions informatisés et du dossier de soin au CHLVO puis progressivement sur l’ensemble des sites de la direction commune soit environ 1100 lits déployés à ce jour. ' WHERE `usr_id` = '1334';
/* 16:23:59 HN */ UPDATE `core_user` SET `usr_biographie` = 'Depuis janvier <strong>2015</strong>, Olivier Plassais exerce la fonction de Directeur des Systèmes d’Information de la Direction Commune : CHD Vendée, CH Les Sables d’Olonne, CH Fontenay le Comte et du Groupe public hospitalier et médico-social des collines vendéennes.<br /><br />\r\nIl a occupé précédemment les postes de : Directeur des Systèmes d’Information du Centre Hospitalier Départemental de Vendée, Directeur des Systèmes d’Information du Centre Hospitalier Loire Vendée Océan, Responsable des Systèmes d’Information des Hôpitaux de CHARTRES,  Responsable des Systèmes d’Information Groupe Caisse de dépôts (SCIC AMO).<br /><br />\r\nAutres fonctions exercées : \r\n<ul>\r\n<li>Délégué régional des pays de la Loire du collège des DSIO de CH, membre du bureau national.</li>\r\n<li>Membre du comité stratégique du GCS E-Santé des Pays de la Loire</li>\r\n<li>Formateur au CNAM de Nantes</li>\r\n</ul> ' WHERE `usr_id` = '1335';
/* 16:24:10 HN */ UPDATE `core_user` SET `usr_biographie` = 'Muriel Forest occupe le poste de chargée d’application informatique parcours de soins dans l’équipe de direction des systèmes informatiques au centre hospitalier de Gonesse (95). De formation d’infirmière et forte d’une expérience de 25 ans dans différentes spécialités (Gynécologie, Obstétrique, pédiatrie grands enfants, pédopsychiatrie pour adolescents), Muriel est devenue cadre en 2006. Elle a également participé activement, pendant 4 ans, à la mise en place de l’informatisation des prescriptions médicales et du dossier de soins dans l’unité de Pneumologie. ' WHERE `usr_id` = '1572';
