<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\ReferenceBundle\Doctrine\Referencement\NoteReader;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use Nodevo\ToolsBundle\Tools\Chaine;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Component\HttpFoundation\Session\Session;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;

/**
 * Manager de l'entité Objet.
 */
class ObjetManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ObjetBundle\Entity\Objet';
    protected $contenuManager;
    protected $noteManager;
    protected $userManager;
    protected $referenceManager;

    /**
     * @var Session Session
     */
    private $session;

    /**
     * @var ObjectIdentityRepository $objectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * Construct.
     *
     * @param EntityManager    $em               Entity Mangager de doctrine
     * @param ContenuManager   $contenuManager   ContenuManager
     * @param NoteManager      $noteManager      NoteManager
     * @param Session          $session          Le service session de Symfony
     * @param UserManager      $userManager
     * @param ReferenceManager $referenceManager
     */
    public function __construct(
        EntityManager $em,
        ContenuManager $contenuManager,
        NoteManager $noteManager,
        Session $session,
        UserManager $userManager,
        ReferenceManager $referenceManager,
        ObjectIdentityRepository $objectIdentityRepository
    ) {
        parent::__construct($em);

        $this->contenuManager = $contenuManager;
        $this->noteManager = $noteManager;
        $this->session = $session;
        $this->userManager = $userManager;
        $this->referenceManager = $referenceManager;
        $this->objectIdentityRepository = $objectIdentityRepository;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau.
     *
     * @param \StdClass $condition
     *
     * @return array
     */
    public function getDatasForGrid(\StdClass $condition = null)
    {
        $domainesIds = $this->userManager->getUserConnected()->getDomainesId();
        $productions = $this->getRepository()->getDatasForGrid($domainesIds, $condition)->getQuery()->getResult();

        $results = [];

        foreach ($productions as $production) {
            /** @var Objet $production */
            $object = [];
            $object['idReference'] = $object['id'] = $production->getId();
            $object['titre'] = $production->getTitre();
            $object['types'] = '';
            $object['domainesNom'] = '';
            $object['isInfraDoc'] = $production->isInfraDoc();
            $object['isArticle'] = $production->isArticle();
            $object['etat'] = $production->getEtat()->getLibelle();
            $object['dateCreation'] = $production->getDateCreation();
            $object['nbVue'] = $production->getNbVue();
            $object['moyenne'] = 0;
            $object['nbNotes'] = 0;
            $object['lock'] = $production->getLock();
            $object['lockedBy'] = $production->getLockedBy();
            $object['dateModification'] = $production->getDateModification();

            foreach ($production->getDomaines() as $domaine) {
                $object['domainesNom'] = $object['domainesNom'] === ''
                    ? $domaine->getNom()
                    : $object['domainesNom'] . ' ; ' . $domaine->getNom()
                ;
            }

            foreach ($production->getTypeLabels() as $type) {
                $object['types'] = $object['types'] === '' ? $type : $object['types'] . ' ; ' . $type;
            }

            $nbNotes = count($production->getListeNotes());

            if ($nbNotes > 0) {
                $totalNotes = 0;
                $object['nbNotes'] = $nbNotes;

                foreach ($production->getListeNotes() as $note) {
                    $totalNotes = $totalNotes + $note->getNote();
                }

                $object['moyenne'] = round(($totalNotes / $nbNotes), 1);
            }

            $results[] = $object;
        }

        return $results;
    }

    /**
     * Retourne la liste des objets.
     *
     * @param array $domains
     *
     * @return array
     */
    public function getObjets($domains = [])
    {
        return $this->getRepository()->getObjets($domains)->getQuery()->getResult();
    }

    public function getObjetsForRSS(Domaine $domaine)
    {
        return $this->getRepository()->getObjetsForRSS($domaine)->getQuery()->getResult();
    }

    /**
     * Récupère les objets pour l'export.
     *
     * @param $ids
     * @param $refsPonderees
     * @param NoteReader $noteReader
     *
     * @return array
     */
    public function getDatasForExport($ids, $refsPonderees, NoteReader $noteReader)
    {
        $objets = $this->getRepository()->getDatasForExport($ids)->getQuery()->getResult();
        $results = [];

        foreach ($objets as $objet) {
            /** @var Objet $objet */
            $row = [];

            //simple stuff
            $row['id'] = $objet->getId();
            $row['titre'] = $objet->getTitre();
            $row['alias'] = $objet->getAlias();
            $row['synthese'] = substr(html_entity_decode($objet->getSynthese()), 0, 40);
            $row['resume'] = substr(html_entity_decode($objet->getResume()), 0, 40);
            $row['commentaires'] = $objet->getCommentaires() ? 'Oui' : 'Non';
            $row['notes'] = $objet->getNotes() ? 'Oui' : 'Non';
            $row['type'] = $objet->isArticle() ? 'Article' : 'Objet';
            $row['nbVue'] = $objet->getNbVue();
            $row['etat'] = $objet->getEtat() ? $objet->getEtat()->getLibelle() : '';
            $row['fichier1'] = $objet->getPath();
            $row['fichier2'] = $objet->getPath2();
            $row['vignette'] = $objet->getVignette();
            $row['dateParution'] = $objet->getDateParution();
            $row['downloadCount1'] = $objet->getDownloadCountFile1();
            $row['downloadCount2'] = $objet->getDownloadCountFile2();

            //quelques Dates
            $row['dateCreation'] = !is_null($objet->getDateCreation())
                ? $objet->getDateCreation()->format('d/m/Y')
                : ''
            ;

            $row['dateModification'] = !is_null($objet->getDateModification())
                ? $objet->getDateModification()->format('d/m/Y')
                : ''
            ;

            //handle Productions liées
            $row['objets'] = json_encode($this->getRelationForObjectExport($objet));

            //handle Roles
            $roles = $objet->getRoles();
            $row['roles'] = [];

            foreach ($roles as $role) {
                $row['roles'][] = $role->getName();
            }
            $row['roles'] = implode(', ', $row['roles']);

            //handle source
            $row['sourceExterne'] = $objet->getSource();

            //handle domaines
            $domaines = $objet->getDomaines();
            $row['domaines'] = [];
            foreach ($domaines as $domaine) {
                $row['domaines'][] = $domaine->getNom();
            }
            $row['domaines'] = implode('|', $row['domaines']);

            //handle note referencement
            $row['note'] = [];
            foreach ($domaines as $domaine) {
                $row['note'][] = $domaine->getNom()
                    . ':'
                    . $noteReader->getNoteByEntityAndDomaineForAffichage($objet, $domaine)
                ;
            }
            $row['note'] = implode('|', $row['note']);

            //handle types (catégories)
            $types = $objet->getTypes();
            $row['types'] = [];
            foreach ($types as $type) {
                $row['types'][] = $type->getLibelle();
            }
            $row['types'] = implode(', ', $row['types']);

            //handle Ambassadeurs concernés
            $ambassadeurs = $objet->getAmbassadeurs();
            $row['ambassadeurs'] = [];
            foreach ($ambassadeurs as $ambassadeur) {
                $row['ambassadeurs'][] = $ambassadeur->getPrenomNom();
            }
            $row['ambassadeurs'] = implode(', ', $row['ambassadeurs']);

            //Récupération de la moyenne des notes de maitrises de cette publication
            $notes = $objet->getMaitriseUsers();
            $row['noteMoyenne'] = 0;
            $row['nombreUserMaitrise'] = 0;
            foreach ($notes as $note) {
                $row['noteMoyenne'] += $note->getPourcentageMaitrise();
                ++$row['nombreUserMaitrise'];
            }
            $row['noteMoyenne'] /= $row['nombreUserMaitrise'] != 0 ? $row['nombreUserMaitrise'] : 1;

            //set empty values for objet (infra doc)
            $row['idParent']
                = $row['idC']
                = $row['titreC']
                = $row['aliasC']
                = $row['orderC']
                = $row['contenuC']
                = $row['dateCreationC']
                = $row['dateModificationC']
                = $row['nbVueC'] = $row['noteC'] = $row['noteMoyenneC'] = $row['nombreNoteC'] = '';

            //Récupération + Calcul note moyenne
            $row['noteMoyenne'] = number_format($this->noteManager->getMoyenneNoteByObjet($objet->getId(), false), 2);
            $row['nombreNote'] = $this->noteManager->countNbNoteByObjet($objet->getId(), false);

            //Fichier modifiable
            $row['referentAnap'] = is_null($objet->getFichierModifiable()) ? ''
                : $objet->getFichierModifiable()->getReferentAnap();
            $row['sourceDocument'] = is_null($objet->getFichierModifiable()) ? ''
                : $objet->getFichierModifiable()->getSourceDocument();
            $row['commentairesFichier'] = is_null($objet->getFichierModifiable()) ? ''
                : $objet->getFichierModifiable()->getCommentaires();
            $row['pathEdit'] = is_null($objet->getFichierModifiable()) ? ''
                : $objet->getFichierModifiable()->getPathEdit();

            $row['module'] = '';
            foreach ($objet->getModules() as $module) {
                $row['module'] .= $module->getId() . ' - ' . $module->getTitre() . ';';
            }

            // Cible de diffusion
            $row['cibleDiffusion'] = is_null($objet->getCibleDiffusion()) ? ''
                : $objet->getCibleDiffusion()->getLibelle();

            // Récupération des commentaires de l'objet
            $row['commentairesAssocies'] = '';
            $commentaires_associes = [];
            foreach ($objet->getListeCommentaires() as $com) {
                $con = $com->getContenu();
                if (empty($con)) {
                    $commentaires_associes[] = $com->getTexte();
                }
            }
            $row['commentairesAssocies'] = implode('|', $commentaires_associes);

            //add Object To Results
            $results[] = $row;

            if ($objet->isInfraDoc()) {
                $contenus = $objet->getContenus();
                if ($contenus) {
                    foreach ($contenus as $contenu) {
                        $rowInfradoc = [];

                        $rowInfradoc['id']
                            = $rowInfradoc['idParent']
                            = $rowInfradoc['titre']
                            = $rowInfradoc['alias']
                            = $rowInfradoc['synthese']
                            = $rowInfradoc['resume']
                            = $rowInfradoc['commentaires']
                            = $rowInfradoc['notes']
                            = $rowInfradoc['type'] = $rowInfradoc['nbVue'] = $rowInfradoc['etat'] = '';
                        $rowInfradoc['dateCreation']
                            = $rowInfradoc['dateParution']
                            = $rowInfradoc['dateModification']
                            = $rowInfradoc['roles']
                            = $rowInfradoc['domaines'] = $rowInfradoc['types'] = $rowInfradoc['ambassadeurs'] = '';
                        $rowInfradoc['fichier1']
                            = $rowInfradoc['fichier2']
                            = $rowInfradoc['vignette']
                            = $rowInfradoc['note']
                            = $rowInfradoc['objets']
                            =
                        $rowInfradoc['noteMoyenne'] = $rowInfradoc['nombreNote'] = $row['nombreUserMaitrise'] = '';
                        $rowInfradoc['referentAnap']
                            = $rowInfradoc['sourceDocument']
                            =
                        $rowInfradoc['commentairesFichier'] = $rowInfradoc['pathEdit'] = $rowInfradoc['module'] = '';

                        //Infra doc values
                        $rowInfradoc['idParent'] = $objet->getId();
                        $rowInfradoc['idC'] = $contenu->getId();
                        $rowInfradoc['titreC'] = $contenu->getTitre();
                        $rowInfradoc['aliasC'] = $contenu->getAlias();
                        $rowInfradoc['orderC'] = $contenu->getOrder();
                        $rowInfradoc['dateCreationC'] = !is_null($contenu->getDateCreation())
                            ? $contenu->getDateCreation()->format('d/m/Y') : '';
                        $rowInfradoc['dateModificationC'] = !is_null($contenu->getDateModification())
                            ? $contenu->getDateModification()->format('d/m/Y') : '';
                        $rowInfradoc['nbVueC'] = $contenu->getNbVue();
                        $rowInfradoc['noteC']
                                                          = null;
                        $rowInfradoc['noteMoyenneC'] = number_format(
                            $this->noteManager->getMoyenneNoteByObjet($contenu->getId(), true),
                            2
                        );
                        $rowInfradoc['nombreNoteC'] = $this->noteManager->countNbNoteByObjet(
                            $contenu->getId(),
                            true
                        );

                        // Récupération des commentaires du contenu
                        $rowInfradoc['commentairesAssocies'] = '';
                        $commentaires_associes = [];
                        foreach ($contenu->getListeCommentaires() as $com) {
                            $commentaires_associes[] = $com->getTexte();
                        }
                        $rowInfradoc['commentairesAssocies'] = implode('|', $commentaires_associes);

                        //add Infra-doc To Results
                        $results[] = $rowInfradoc;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @param Objet $object
     *
     * @return array
     */
    private function getRelationForObjectExport(Objet $object)
    {
        $relations = $this->objectIdentityRepository->getRelatedObjects(ObjectIdentity::createFromDomainObject($object), [
            Objet::class,
            Contenu::class,
        ]);

        $list = [];
        foreach ($relations as $relation) {
            if ($relation->getObject() instanceof Contenu) {
                $list[] = sprintf('INFRADOC:%s', $relation->getObjectId());
            } else {
                if ($relation->getObject()->isArticle()) {
                    $list[] = sprintf('ARTICLE:%s', $relation->getObjectId());
                } else {
                    $list[] = sprintf('PUBLICATION:%s', $relation->getObjectId());
                }
            }
        }

        return $list;
    }

    /**
     * Retourne la liste des objets selon le/les types.
     *
     * @param array $types Les types à filtrer
     * @param int   $limit
     * @param array $order
     * @param array $domains
     *
     * @return array
     */
    public function getObjetsByTypes(
        $types,
        $limit = 0,
        $order = ['champ' => 'obj.dateModification', 'tri' => 'DESC'],
        $domains = []
    ) {
        return $this->getRepository()->getObjetsByTypes($types, $limit, $order, $domains)->getQuery()->getResult();
    }

    /**
     * Retourne l'ensemble des productions actives.
     */
    public function getProductionsActive()
    {
        return $this->getRepository()->getProductionsActive()->getQuery()->getResult();
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param null $condition
     *
     * @return array
     */
    public function getDatasForGridAmbassadeur($condition = null)
    {
        $results = $this->getRepository()->getDatasForGridAmbassadeur($condition)->getQuery()->getResult();

        return $this->rearangeForTypes($results);
    }

    /**
     * Vérouille un objet en accès.
     *
     * @param Objet $objet Objet concerné
     * @param User  $user  User concerné
     *
     * @return Objet
     */
    public function lock($objet, $user)
    {
        $objet->setLock(1);
        $objet->setLockedBy($user);

        $this->save($objet);

        return $objet;
    }

    /**
     * Dévérouille un objet en accès.
     *
     * @param Objet $objet Objet concerné
     */
    public function unlock($objet)
    {
        if (!is_null($objet)) {
            $objet->setLock(0);
            $objet->setLockedBy(null);

            $this->save($objet);
        }
    }

    /**
     * Retourne la liste des objets pour un ambassadeur donné.
     *
     * @param int $idUser Id de l'ambassadeur
     *
     * @return array
     */
    public function getObjetsByAmbassadeur($idUser)
    {
        return $this->getRepository()->getObjetsByAmbassadeur($idUser)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des objets non maitrisés par l'ambassadeur.
     *
     * @param int   $id    Id de l'ambassadeur
     * @param array $types Liste des types
     *
     * @return array
     */
    public function getObjetsNonMaitrises($id, $types)
    {
        $results = $this->getProductions($types);
        $objets = [];

        foreach ($results as $one) {
            $add = true;
            $ambassadeurs = $one->getAmbassadeurs();
            if (count($ambassadeurs) >= 1) {
                foreach ($ambassadeurs as $ambassadeur) {
                    if ($ambassadeur->getId() == $id) {
                        $add = false;
                        break;
                    }
                }
            }

            if ($add) {
                $objet = new \stdClass();
                $objet->id = $one->getId();
                $objet->titre = $one->getTitre();
                $objets[] = $objet;
            }
        }

        return $objets;
    }

    /**
     * Retourne la liste des objets non maitrisés par domaine.
     *
     * @param int   $id    Id de
     * @param array $types Liste des types
     *
     * @return array
     */
    public function getObjetsNonMaitrisesByDomaine($id, $types, $domaines)
    {
        // $results = $this->getProductions($types);
        foreach ($types as $key => $type) {
            if ($type->getId() == 183 || $type->getId() == 184) {
                unset($types[$key]);
            }
        }

        $objets = $this->getRepository()->getObjetsByTypeAmbassadeursAndDomaines($types, $id, $domaines);

        return $objets;
    }

    /**
     * Retourne la liste des productions.
     *
     * @param array $types Liste des types
     *
     * @return array
     */
    public function getProductions($types)
    {
        //Remove Points Dur et Ressources Externes
        foreach ($types as $key => $type) {
            if ($type->getId() == 183 || $type->getId() == 184) {
                unset($types[$key]);
            }
        }

        return $this->getObjetsByTypes($types);
    }

    /**
     * Vérifie que le rôle ne fait pas partie de la liste des rôles exclus.
     *
     * @param string $role  Rôle de l'user connecté
     * @param Objet  $objet L'entitée Objet
     *
     * @return bool
     */
    public function checkAccessToObjet($role, $objet)
    {
        //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
        if (is_null($objet)) {
            $this->session->getFlashBag()->add('danger', 'Vous tentez de rejoindre une page qui n\'existe plus.');

            return false;
        }
        $roles = $objet->getRoles();
        foreach ($roles as $restrictedRole) {
            //on "break" en retournant null, l'objet n'est pas ajouté
            if ($restrictedRole->getRole() == $role) {
                return false;
            }
        }

        return true;
    }

    /**
     * Formatte les types d'objet sous forme d'une chaine de caractère avec le séparateur $sep.
     *
     * @param array  $types Types de l'objet
     * @param string $sep   Séparateur pour l'implode
     *
     * @return string
     */
    public function formatteTypes($types, $sep = ' ♦ ')
    {
        $tabType = [];
        foreach ($types as $type) {
            $tabType[] = $type->getLibelle();
        }

        return implode($sep, $tabType);
    }

    /**
     * [testAliasExist description].
     *
     * @param [type] $objet [description]
     * @param [type] $new   [description]
     *
     * @return [type]
     */
    public function testAliasExist($objet, $new)
    {
        $alias = $this->findOneBy(['alias' => $objet->getAlias()]);

        if ($alias && $new === true) {
            return true;
        } elseif ($alias && $new === false && $alias->getId() != $objet->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Retourne l'arbo Objets -> contenus.
     *
     * @param Reference[] $types
     * @param Objet|null  $object
     *
     * @return array
     */
    public function getObjetsAndContenuArbo($types = null, Objet $object = null)
    {
        //@todo Vérif pour remplacer $this->findAll() qui pourrait générer des centaines de requêtes
        //get objets and IDS

        $objectDomains = is_null($object) ? [] : $object->getDomaines();

        if (is_null($types)) {
            $objets = count($objectDomains) > 0 ? $this->getObjets($objectDomains) : $this->getObjets();
        } else {
            $objets = count($objectDomains) > 0 ? $this->getObjetsByTypes(
                $types,
                0,
                ['champ' => 'obj.dateModification', 'tri' => 'DESC'],
                $objectDomains
            ) : $this->getObjetsByTypes($types);
        }

        $ids = [];
        foreach ($objets as $one) {
            $ids[] = $one->getId();
        }

        //get Contenus
        $datas = $this->contenuManager->getArboForObjet($ids);
        $contenus = [];
        foreach ($datas as $one) {
            if ($one->objet != null) {
                $contenus[$one->objet][] = $one;
            }
        }

        $results = [];

        //formate datas
        foreach ($objets as $one) {
            //Traitement pour Article
            if ($one->isArticle()) {
                $results[] = [
                    'text' => $one->getTitre(),
                    'value' => 'ARTICLE:' . $one->getId(),
                ];
            } else { //Traitement pour Publication et Infradoc
                $results[] = [
                    'text' => $one->getTitre(),
                    'value' => 'PUBLICATION:' . $one->getId(),
                ];

                if (!isset($contenus[$one->getId()]) || count($contenus[$one->getId()]) <= 0) {
                    continue;
                }

                foreach ($contenus[$one->getId()] as $content) {
                    $results[] = [
                        'text' => '|--' . $content->titre,
                        'value' => 'INFRADOC:' . $content->id,
                    ];
                    $this->getObjetsChilds($results, $content, 2);
                }
            }
        }

        return $results;
    }

    /**
     * @return array<string, string>
     */
    public function getObjetsAndContenuForFormTypeChoices()
    {
        $objetsAndContenuForFormTypeChoices = [];
        $objetsAndContenuArbo = $this->getObjetsAndContenuArbo();

        foreach ($objetsAndContenuArbo as $objetOrContenu) {
            $objetsAndContenuForFormTypeChoices[$objetOrContenu['value']] = $objetOrContenu['text'];
        }

        return $objetsAndContenuForFormTypeChoices;
    }

    /**
     * Récupération du nombre de vue total de toutes les publications.
     *
     * @return int
     */
    public function getNbVuesPublication()
    {
        return $this->getRepository()->getNbVuesPublication()->getQuery()->getSingleScalarResult();
    }

    /**
     * Retorune l'arbo des articles.
     *
     * @param $types
     *
     * @return array
     */
    public function getArticlesArbo($types)
    {
        //get objets
        $objets = $this->getObjetsByTypes($types);
        $results = [];

        //formate datas
        foreach ($objets as $one) {
            $results[] = [
                'text' => $one->getTitre(), 'value' => 'ARTICLE:' . $one->getId(),
            ];
        }

        return $results;
    }

    /**
     * Retourne la liste des actualités des catégories passées en paramètre.
     *
     * @param array   $categories Les catégories
     * @param         $role
     * @param int     $limit
     * @param array   $order
     * @param Domaine $domain
     *
     * @return array
     */
    public function getActualitesByCategorie(
        $categories,
        $role,
        $limit = 0,
        $order = ['champ' => 'obj.dateModification', 'tri' => 'DESC'],
        Domaine $domain = null
    ) {
        $articles = $this->getObjetsByTypes($categories, $limit, $order);
        $actualites = [];

        /** @var Objet $article */
        foreach ($articles as $article) {
            if ($this->checkAccessToObjet($role, $article)
                && (is_null($domain)
                    || $article->getDomaines()->contains(
                        $domain
                    )
                )
            ) {
                $actu = new \stdClass();

                $actu->id = $article->getId();
                $actu->titre = $article->getTitre();
                $actu->alias = $article->getAlias();
                $actu->date = (is_null($article->getDateModification())) ? $article->getDateCreation()
                    : $article->getDateModification();
                $actu->image = $article->getVignette() ? $article->getVignette() : false;

                //resume
                $tab = explode('<!-- pagebreak -->', $article->getResume());
                $actu->resume = $tab[0];
                $actu->hasPageBreak = strpos($article->getResume(), '<!-- pagebreak -->') !== false;

                //types / catégories
                $types = $article->getTypes();
                $actu->types = $this->formatteTypes($types);
                $actu->categories = $this->getCategorieForUrl($article->getTypes());

                $actualites[] = $actu;
            }
        }

        usort($actualites, [$this, 'triArrayObjetDateAntichronologique']);

        return $actualites;
    }

    /**
     * Retourne les catégories qui ont des articles.
     *
     * @param array        $allCategories Liste des catégories
     * @param Domaine|null $domain
     *
     * @return array
     */
    public function getCategoriesWithArticles($allCategories, Domaine $domain = null)
    {
        $categories = [];
        /** @var Reference $one */
        foreach ($allCategories as $one) {
            $articles = $this->getObjetsByTypes([$one]);

            if (count($articles) > 0 && (is_null($domain) || $one->getDomaines()->contains($domain))) {
                $categ = new \stdClass();
                $categ->id = $one->getId();
                $categ->libelle = $one->getLibelle();

                $categories[$one->getOrder()] = $categ;
            }
        }

        ksort($categories);

        return $categories;
    }

    /**
     * Retourne l'objet article pour la page d'accueil.
     *
     * @return \stdClass
     */
    public function getArticleHome()
    {
        $article = $this->findOneBy(['id' => 1]);
        $item = new \stdClass();

        $item->id = $article->getId();
        $item->titre = $article->getTitre();
        $item->alias = $article->getAlias();
        $item->image = $article->getVignette() ? $article->getVignette() : false;
        $item->categories = $this->getCategorieForUrl($article->getTypes());

        //resume
        $tab = explode('<!-- pagebreak -->', $article->getResume());
        $item->resume = $tab[0];
        $item->hasPageBreak = strpos($article->getResume(), '<!-- pagebreak -->') !== false;

        return $item;
    }

    /**
     * Retourne la note de l'objet.
     *
     * @param array $references   Tableau des références
     * @param array $ponderations Tableau des pondérations
     *
     * @return int
     */
    public function getNoteReferencement($references, $ponderations)
    {
        $note = 0;
        foreach ($references as $reference) {
            $id = $reference->getReference()->getId();

            if (isset($ponderations[$id])) {
                $note += $ponderations[$id]['poids'];
            }
        }

        return $note;
    }

    public function getProductionsLiees(Objet $objet)
    {
        return $this->getRepository()->getProductionsLiees($objet);
    }

    /**
     * Formatte les productions pour l'affichage des productions liées.
     *
     * @param array $datas Liste des prod liées
     *
     * @return array
     */
    public function formatteProductionsLiees($datas)
    {
        $productions = [];

        foreach ($datas as $one) {
            //explode to get datas
            $tab = explode(':', $one);

            //build new object
            $element = new \StdClass();
            $element->id = $tab[1];
            $element->brut = $one;

            //switch Objet / Infra-doc
            if ($tab[0] == 'PUBLICATION') {
                $objet = $this->findOneBy(['id' => $tab[1]]);
                if (null === $objet) {
                    $element->titre = 'La publication ' . $tab[1] . ' a été supprimée';
                } else {
                    $element->titre = $objet->getTitre();
                }
                $element->isObjet = 1;
            } elseif ($tab[0] == 'INFRADOC') {
                $contenu = $this->contenuManager->findOneBy(['id' => $tab[1]]);
                if (null === $contenu) {
                    $element->titre = 'Le contenu ' . $tab[1] . ' a été supprimé';
                }
                $element->titre = '|--' . $contenu->getTitre();
                $element->isObjet = 0;
            } elseif ($tab[0] == 'ARTICLE') {
                $objet = $this->findOneBy(['id' => $tab[1]]);
                if (null === $objet) {
                    $element->titre = 'L\'article ' . $tab[1] . ' a été supprimé';
                }
                $element->titre = $objet->getTitre();
                $element->isObjet = 1;
            }

            $productions[] = $element;
        }

        return $productions;
    }

    /**
     * Retourne les articles d'une catégorie.
     *
     * @param Reference $categorie Catégorie
     * @param Domaine   $domaine   Domaine
     *
     * @return array <\HopitalNumerique\ObjetBundle\Entity\Objet> Articles
     */
    public function getArticlesForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getRepository()->getArticlesForCategorie($categorie, $domaine);
    }

    /**
     * Retourne le dernier article d'une catégorie.
     *
     * @param Reference $categorie Catégorie
     * @param Domaine   $domaine   Domaine
     *
     * @return Objet Dernier article
     */
    public function getLastArticleForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getRepository()->getLastArticleForCategorie($categorie, $domaine);
    }

    /**
     * Retourne les infradocs d'un domaine.
     *
     * @param Domaine $domaine Domaine
     *
     * @return array<\HopitalNumerique\ObjetBundle\Entity\Objet> Infradocs
     */
    public function getInfradocs(Domaine $domaine)
    {
        return $this->getRepository()->getInfradocs($domaine);
    }

    private function triArrayObjetDateAntichronologique($a, $b)
    {
        return $a->date > $b->date ? 0 : 1;
    }

    /**
     * Formatte les types de l'objet pour les URLS (catégorie param).
     *
     * @param array $types Les types de l'objet
     *
     * @return string
     */
    private function getCategorieForUrl($types)
    {
        $type = $types[0];
        $categorie = '';

        foreach ($type->getParents() as $typeParent) {
            $categorie .= $typeParent->getLibelle() . '-';
        }
        $categorie .= $type->getLibelle();

        $tool = new Chaine($categorie);

        return $tool->minifie();
    }

    /**
     * Ajoute les enfants de $objet dans $return, formatées en fonction de $level.
     *
     * @param array     $return
     * @param \stdClass $objet
     * @param int       $level
     */
    private function getObjetsChilds(&$return, $objet, $level = 1)
    {
        if (count($objet->childs) > 0) {
            foreach ($objet->childs as $child) {
                $texte = str_pad($child->titre, strlen($child->titre) + ($level * 3), '|--', STR_PAD_LEFT);
                $return[] = [
                    'text' => $texte, 'value' => 'INFRADOC:' . $child->id,
                ];
                $this->getObjetsChilds($return, $child, $level + 1);
            }
        }
    }

    /**
     * Réarrange les objets pour afficher correctement les types.
     *
     * @param array $results Les résultats de la requete
     *
     * @return array
     */
    private function rearangeForTypes($results)
    {
        $objets = [];

        foreach ($results as $result) {
            if (isset($objets[$result['id']])) {
                $objets[$result['id']]['types'] .= ', ' . $result['types'];
            } else {
                $objets[$result['id']] = $result;
            }
        }

        return array_values($objets);
    }

    /**
     * [getChilds description].
     *
     * @param [type] $retour [description]
     * @param [type] $elem   [description]
     *
     * @return [type]
     */
    private function getChilds(&$retour, $elem)
    {
        if (isset($elem['childs']) && count($elem['childs'])) {
            $childs = [];
            foreach ($elem['childs'] as $key => $one) {
                $childs[$one] = $retour[$one];
                $petitsEnfants = $this->getChilds($retour, $childs[$one]);
                if ($petitsEnfants) {
                    $childs[$one]['childs'] = $petitsEnfants;
                    unset($retour[$one]);
                } else {
                    unset($retour[$one]);
                }
            }

            return $childs;
        } else {
            return false;
        }
    }

    /**
     * Enregistre l'entitée.
     *
     * @param $entity
     */
    public function save($entity)
    {
        if (is_array($entity)) {
            foreach ($entity as $one) {
                if ($one->getAlaune() == 1) {
                    $this->setAllAlaUneFalse($one->getId());
                }
            }
            $this->em->persist($one);
        } else {
            if ($entity->getAlaune() == 1) {
                $this->setAllAlaUneFalse($entity->getId());
            }

            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    /**
     * Set le champ A la une à false pour tous les contenus.
     *
     * @param $id
     *
     * @return array
     */
    public function setAllAlaUneFalse($id)
    {
        return $this->getRepository()->setAllAlaUneFalse($id)->getQuery()->getResult();
    }

    /**
     * Retourne l'article à la une.
     *
     * @return Objet[]
     */
    public function getArticleAlaUne()
    {
        return $this->getRepository()->getArticleAlaUne()->getQuery()->getResult();
    }

    /**
     * Retourne les articles du domaine.
     *
     * @return Objet[]
     */
    public function getObjetByDomaine()
    {
        $objets = $this->getRepository()->getObjetByDomaine()->getQuery()->getResult();
        $ids = [];
        foreach ($objets as $one) {
            $ids[] = $one->getId();
        }

            //get Contenus
            $datas = $this->contenuManager->getArboForObjet($ids);
        $contenus = [];
        foreach ($datas as $one) {
            if ($one->objet != null) {
                $contenus[$one->objet][] = $one;
            }
        }

        $results = [];

        //formate datas
        foreach ($objets as $one) {
            //Traitement pour Article
            if ($one->isArticle()) {
                $results[] = [
                    'text' => $one->getTitre(),
                    'value' => 'ARTICLE:' . $one->getId(),
                ];
            } //Traitement pour Publication et Infradoc
            else {
                $results[] = [
                    'text' => $one->getTitre(),
                    'value' => 'PUBLICATION:' . $one->getId(),
                ];

                if (!isset($contenus[$one->getId()]) || count($contenus[$one->getId()]) <= 0) {
                    continue;
                }

                foreach ($contenus[$one->getId()] as $content) {
                    $results[] = [
                        'text' => '|--' . $content->titre,
                        'value' => 'INFRADOC:' . $content->id,
                    ];
                    $this->getObjetsChilds($results, $content, 2);
                }
            }
        }

        return $results;
    }

    /**
     * Retourne les objets des types données et du domaine.
     *
     * @param array $types
     * @param int   $idDomaine
     *
     * @return Objet[]
     */
    public function getObjetsByTypesAndDomaine($types, $idDomaine = null)
    {
        if (is_null($idDomaine)) {
            $idDomaine = $this->session->get('domaineId');
        }

        return $this->getRepository()->getObjetsByTypesAndDomaine($types, $idDomaine);
    }

    /**
     * @return ObjetRepository
     */
    protected function getRepository()
    {
        return $this->em->getRepository(Objet::class);
    }

    /**
     * @param Objet $objet
     *
     * @return array
     */
    public function getObjectRelationships(Objet $objet)
    {
        $objectRelationships = $this->getRepository()->getObjectRelationships();

        $relatedObjectIds = [];
        $relatedObjects = [];
        foreach ($objectRelationships as $objectRelationship) {
            foreach ($objectRelationship['objets'] as $relation) {
                if (explode(':', $relation)[1] == $objet->getId()) {
                    $relatedObjectIds[] = $objectRelationship['id'];
                }
            }
        }

        $relatedObjectIds = array_unique($relatedObjectIds);

        foreach ($relatedObjectIds as $objectId) {
            $relatedObjects[] = $this->getRepository()->findOneBy(
                ['id' => $objectId]
            );
        }

        return $relatedObjects;
    }
}
