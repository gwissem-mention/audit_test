<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use \Nodevo\ToolsBundle\Tools\Chaine;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;


use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Manager de l'entité Objet.
 */
class ObjetManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Objet';
    protected $_contenuManager;
    protected $_noteManager;
    protected $_userManager;
    protected $_referenceManager;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session Session
     */
    private $_session;

    /**
     * Construct 
     *
     * @param EntityManager  $em              Entity Mangager de doctrine
     * @param ContenuManager $contenuManager  ContenuManager
     * @param NoteManager    $noteManager     NoteManager
     * @param \Symfony\Component\HttpFoundation\Session\Session $session       Le service session de Symfony
     */
    public function __construct( EntityManager $em, ContenuManager $contenuManager, NoteManager $noteManager, Session $session, UserManager $userManager, ReferenceManager $referenceManager)
    {
        parent::__construct($em);

        $this->_contenuManager   = $contenuManager;
        $this->_noteManager      = $noteManager;
        $this->_session          = $session;
        $this->_userManager      = $userManager;
        $this->_referenceManager = $referenceManager;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $resultatsForGrid        = array();
        $userConnectedDomainesId = $this->_userManager->getUserConnected()->getDomainesId();
        $results                 = $this->getRepository()->getDatasForGrid( $userConnectedDomainesId, $condition )->getQuery()->getResult();

        foreach ($results as $result) 
        {
            if(!array_key_exists($result['id'], $resultatsForGrid))
            {
                $resultatsForGrid[$result['id']] = $result;
                $resultatsForGrid[$result['id']]['moyenne'] = number_format($this->_noteManager->getMoyenneNoteByObjet($result['id'], false),2);
                $resultatsForGrid[$result['id']]['nbNotes'] = $this->_noteManager->countNbNoteByObjet($result['id'], false);
            }
            elseif(trim($result['domaineNom']) != '')
            {
                $resultatsForGrid[$result['id']]['domaineNom'] .= ";" . $result['domaineNom'];
            }

            $resultatsForGrid[$result['id']]['idObjet'] = $result['id'];
        }

        $results = array_merge($resultatsForGrid);

        return $this->rearangeForTypes( $results );
    }

    /**
     * Retourne la liste des objets
     *
     * @return array
     */
    public function getObjets()
    {
    	return $this->getRepository()->getObjets()->getQuery()->getResult();
    } 
    
    /**
     * Récupère les objets pour le dashboard Back
     *
     * @return array
     */
    public function getObjetsForDashboard()
    {
        return $this->getRepository()->getObjetsForDashboard()->getQuery()->getResult();
    }

    public function getObjetsForRSS(Domaine $domaine)
    {
        return $this->getRepository()->getObjetsForRSS($domaine)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des objets en fonction des dates passées en param
     *
     * @param DateTime $dateDebut Date début fourchette
     * @param DateTime $dateFin   Date fin fourchette
     *
     * @return array
     */
    public function getObjetsByDate($dateDebut, $dateFin)
    {
        return $this->getRepository()->getObjetsByDate($dateDebut, $dateFin)->getQuery()->getResult();
    }

    /**
     * Récupère les objets pour l'export
     *
     * @return array
     */
    public function getDatasForExport( $ids, $refsPonderees )
    {
        $objets  = $this->getRepository()->getDatasForExport( $ids )->getQuery()->getResult();
        $results = array();

        foreach($objets as $objet) {
            $row = array();

            //simple stuff
            $row['id']           = $objet->getId();
            $row['titre']        = $objet->getTitre();
            $row['alias']        = $objet->getAlias();
            $row['synthese']     = substr(html_entity_decode($objet->getSynthese()),0 , 40);
            $row['resume']       = substr(html_entity_decode($objet->getResume()),0 , 40);
            $row['commentaires'] = $objet->getCommentaires() ? 'Oui' : 'Non';
            $row['notes']        = $objet->getNotes()        ? 'Oui' : 'Non';
            $row['type']         = $objet->isArticle()       ? 'Article' : 'Objet';
            $row['nbVue']        = $objet->getNbVue();
            $row['etat']         = $objet->getEtat() ? $objet->getEtat()->getLibelle() : '';
            $row['fichier1']     = $objet->getPath();
            $row['fichier2']     = $objet->getPath2();
            $row['vignette']     = $objet->getVignette();
            $row['note']         = number_format($this->getNoteReferencement($objet->getReferences(), $refsPonderees), 0);
            $row['dateParution'] = $objet->getDateParution();

            //quelques Dates
            $row['dateCreation']         = !is_null($objet->getDateCreation())         ? $objet->getDateCreation()->format('d/m/Y')         : '';
            $row['dateDebutPublication'] = !is_null($objet->getDateDebutPublication()) ? $objet->getDateDebutPublication()->format('d/m/Y') : '';
            $row['dateFinPublication']   = !is_null($objet->getDateFinPublication())   ? $objet->getDateFinPublication()->format('d/m/Y')   : '';
            $row['dateModification']     = !is_null($objet->getDateModification())     ? $objet->getDateModification()->format('d/m/Y')     : '';
            
            //handle Productions liées
            $row['objets'] = json_encode($objet->getObjets());

            //handle Roles
            $roles        = $objet->getRoles();
            $row['roles'] = array();
            foreach($roles as $role)
            {
                $row['roles'][] = $role->getName();
            }
            $row['roles'] = implode(', ', $row['roles']);

            //handle source
            $row['sourceExterne'] = $objet->getSource();
            
            //handle domaines
            $domaines = $objet->getDomaines();
            $row['domaines'] = array();
            foreach ($domaines as $domaine) 
            {
                $row['domaines'][] = $domaine->getNom();
            }
            $row['domaines'] = implode('|', $row['domaines']);
                
            //handle types (catégories)
            $types        = $objet->getTypes();
            $row['types'] = array();
            foreach($types as $type)
                $row['types'][] = $type->getLibelle();
            $row['types'] = implode(', ', $row['types']);

            //handle Ambassadeurs concernés
            $ambassadeurs        = $objet->getAmbassadeurs();
            $row['ambassadeurs'] = array();
            foreach($ambassadeurs as $ambassadeur)
                $row['ambassadeurs'][] = $ambassadeur->getPrenomNom();
            $row['ambassadeurs'] = implode(', ', $row['ambassadeurs']);

            //Récupération de la moyenne des notes de maitrises de cette publication
            $notes                     = $objet->getMaitriseUsers();
            $row['noteMoyenne']        = 0;
            $row['nombreUserMaitrise'] = 0;
            foreach ($notes as $note) {
                $row['noteMoyenne'] += $note->getPourcentageMaitrise();
                $row['nombreUserMaitrise']++;
            }
            $row['noteMoyenne'] /= $row['nombreUserMaitrise'] != 0 ? $row['nombreUserMaitrise'] : 1;

            //set empty values for objet (infra doc)
            $row['idParent'] = $row['idC'] = $row['titreC'] = $row['aliasC'] = $row['orderC'] = $row['contenuC'] = $row['dateCreationC'] = $row['dateModificationC'] = $row['nbVueC'] = $row['noteC']= $row['noteMoyenneC']= $row['nombreNoteC'] = '';

            //Récupération + Calcul note moyenne
            $row['noteMoyenne'] = number_format($this->_noteManager->getMoyenneNoteByObjet($objet->getId(), false),2);
            $row['nombreNote']  = $this->_noteManager->countNbNoteByObjet($objet->getId(), false);

            //Fichier modifiable
            $row['referentAnap']   = is_null($objet->getFichierModifiable()) ? '' : $objet->getFichierModifiable()->getReferentAnap();
            $row['sourceDocument'] = is_null($objet->getFichierModifiable()) ? '' : $objet->getFichierModifiable()->getSourceDocument();
            $row['commentairesFichier']   = is_null($objet->getFichierModifiable()) ? '' : $objet->getFichierModifiable()->getCommentaires();
            $row['pathEdit']       = is_null($objet->getFichierModifiable()) ? '' : $objet->getFichierModifiable()->getPathEdit();

            $row['module'] = "";
            foreach ($objet->getModules() as $module) 
            {
                $row['module'] .= $module->getId() . ' - ' . $module->getTitre() . ';';
            }

            // Cible de diffusion
            $row['cibleDiffusion'] = is_null($objet->getCibleDiffusion()) ? '' : $objet->getCibleDiffusion()->getLibelle();

            // Récupération des commentaires de l'objet
            $row['commentairesAssocies'] = "";
            $commentaires_associes = array();
            foreach ($objet->getListeCommentaires() as $com) {
              $con = $com->getContenu();
              if(empty($con)) {
                $commentaires_associes[] = $com->getTexte();
              }
            }
            $row['commentairesAssocies'] = implode('|',$commentaires_associes);

            //add Object To Results
            $results[] = $row;

            if( $objet->isInfraDoc() ) {
                $contenus = $objet->getContenus();
                if( $contenus ){
                    foreach($contenus as $contenu) {                        
                        $rowInfradoc = array();

                        $rowInfradoc['id'] = $rowInfradoc['idParent'] = $rowInfradoc['titre'] = $rowInfradoc['alias'] = $rowInfradoc['synthese'] = $rowInfradoc['resume'] = $rowInfradoc['commentaires'] = $rowInfradoc['notes'] = $rowInfradoc['type'] = $rowInfradoc['nbVue'] = $rowInfradoc['etat'] = '';
                        $rowInfradoc['dateCreation'] = $rowInfradoc['dateParution'] = $rowInfradoc['dateDebutPublication'] = $rowInfradoc['dateFinPublication'] = $rowInfradoc['dateModification'] = $rowInfradoc['roles'] = $rowInfradoc['domaines'] = $rowInfradoc['types'] = $rowInfradoc['ambassadeurs'] = '';
                        $rowInfradoc['fichier1'] = $rowInfradoc['fichier2'] = $rowInfradoc['vignette'] = $rowInfradoc['note'] = $rowInfradoc['objets'] = $rowInfradoc['noteMoyenne'] = $rowInfradoc['nombreNote'] = $row['nombreUserMaitrise'] = '';
                        $rowInfradoc['referentAnap'] = $rowInfradoc['sourceDocument'] = $rowInfradoc['commentairesFichier'] =  $rowInfradoc['pathEdit'] =  $rowInfradoc['module'] = '';

                        //Infra doc values
                        $rowInfradoc['idParent']          = $objet->getId();
                        $rowInfradoc['idC']               = $contenu->getId();
                        $rowInfradoc['titreC']            = $contenu->getTitre();
                        $rowInfradoc['aliasC']            = $contenu->getAlias();
                        $rowInfradoc['orderC']            = $contenu->getOrder();
                        $rowInfradoc['dateCreationC']     = !is_null($contenu->getDateCreation()) ? $contenu->getDateCreation()->format('d/m/Y') : '';
                        $rowInfradoc['dateModificationC'] = !is_null($contenu->getDateModification()) ? $contenu->getDateModification()->format('d/m/Y') : '';
                        $rowInfradoc['nbVueC']            = $contenu->getNbVue();
                        $rowInfradoc['noteC']             = number_format($this->getNoteReferencement($contenu->getReferences(), $refsPonderees), 0);
                        $rowInfradoc['noteMoyenneC']      = number_format($this->_noteManager->getMoyenneNoteByObjet($contenu->getId(), true),2);
                        $rowInfradoc['nombreNoteC']       = $this->_noteManager->countNbNoteByObjet($contenu->getId(), true);

                        // Récupération des commentaires du contenu
                        $rowInfradoc['commentairesAssocies'] = "";
                        $commentaires_associes = array();
                        foreach ($contenu->getListeCommentaires() as $com) {
                          $commentaires_associes[] = $com->getTexte();
                        }
                        $rowInfradoc['commentairesAssocies'] = implode('|',$commentaires_associes);

                        //add Infra-doc To Results
                        $results[] = $rowInfradoc;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Retourne la liste des objets selon le/les types
     *
     * @param array $types Les types à filtrer
     *
     * @return array
     */
    public function getObjetsByTypes( $types, $limit = 0 , $order = array( 'champ' => 'obj.dateModification', 'tri' => 'DESC'))
    {
        return $this->getRepository()->getObjetsByTypes( $types, $limit, $order )->getQuery()->getResult();
    }

    /**
     * Retourne la liste des objets selon le/les types et trié par nombre de vu
     *
     * @param array $types Les types à filtrer
     *
     * @return array
     */
    public function getObjetsByNbVue( $limit = 0 )
    {
      return $this->getRepository()->getObjetsByNbVue( $limit )->getQuery()->getResult();
    }
    
    /**
     * Retourne l'ensemble des productions actives
     */
    public function getProductionsActive()
    {
        return $this->getRepository()->getProductionsActive()->getQuery()->getResult();
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGridAmbassadeur( $condition = null )
    {
        $results = $this->getRepository()->getDatasForGridAmbassadeur( $condition )->getQuery()->getResult();
        
        return $this->rearangeForTypes( $results );
    }

    /**
     * Vérouille un objet en accès
     *
     * @param Objet $objet Objet concerné
     * @param User  $user  User concerné
     *
     * @return Objet
     */
    public function lock( $objet, $user )
    {
        $objet->setLock( 1 );
        $objet->setLockedBy( $user );

        $this->save( $objet );

        return $objet;
    }

    /**
     * Dévérouille un objet en accès
     *
     * @param Objet $objet Objet concerné
     *
     * @return empty
     */
    public function unlock( $objet )
    {
        if(!is_null($objet))
        {
            $objet->setLock( 0 );
            $objet->setLockedBy( null );

            $this->save( $objet );
        }
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param objet $objet      Objet concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($objet, $references)
    {
        $selectedReferences = $objet->getReferences();

        //applique les références 
        foreach( $selectedReferences as $selected )
        {
            //on récupère l'élément que l'on va manipuler
            $ref = $references[ $selected->getReference()->getId() ];

            //on le met à jour 
            $ref->selected = true;
            $ref->primary  = $selected->getPrimary();

            //on remet l'élément à sa place
            $references[ $selected->getReference()->getId() ] = $ref;
        }

        $references = $this->filtreReferencesByDomaines($objet, $references);
        
        return $references;
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param objet $objet      Objet concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferencesOwn($objet)
    {
        $return = array();
        $selectedReferences = $objet->getReferences();

        //applique les références 
        foreach( $selectedReferences as $selected ){
            $reference = $selected->getReference();

            //on remet l'élément à sa place
            $return[ $reference->getId() ]['nom']     = $reference->getCode() . " - " . $reference->getLibelle();
            $return[ $reference->getId() ]['primary'] = $selected->getPrimary();
            
            if( $reference->getParent() )
                $return[ $reference->getParent()->getId() ]['childs'][] = $reference->getId();
        }
        
        $this->formatReferencesOwn( $return );
        
        return $return;
    }
    
    /**
     * Retourne la liste des objets pour un ambassadeur donné
     * 
     * @param integer $idUser Id de l'ambassadeur
     */
    public function getObjetsByAmbassadeur( $idUser )
    { 
        return $this->getRepository()->getObjetsByAmbassadeur( $idUser )->getQuery()->getResult();
    }
    
    /**
     * Retourne la liste des objets non maitrisés par l'ambassadeur
     *
     * @param integer $id    Id de l'ambassadeur
     * @param array   $types Liste des types
     *
     * @return array
     */
    public function getObjetsNonMaitrises( $id, $types )
    {
        $results = $this->getProductions( $types );
        $objets  = array();

        foreach ($results as $one)
        {
            $add = true;
            $ambassadeurs = $one->getAmbassadeurs();
            if( count($ambassadeurs) >= 1 ){
                foreach($ambassadeurs as $ambassadeur){
                    if( $ambassadeur->getId() == $id ){
                        $add = false;
                        break;
                    }
                }
            }

            if( $add ) {
                $objet        = new \stdClass;
                $objet->id    = $one->getId();
                $objet->titre = $one->getTitre();
                $objets[]     = $objet;
            }
        }

        return $objets;
    }

    /**
     * Retourne la liste des productions
     *
     * @param array   $types Liste des types
     *
     * @return array
     */
    public function getProductions( $types )
    {
        //Remove Points Dur et Ressources Externes
        foreach($types as $key => $type){
            if( $type->getId() == 183 || $type->getId() == 184)
                unset($types[$key]);
        }

        return $this->getObjetsByTypes( $types );
    }

    /**
     * Vérifie que le rôle ne fait pas partie de la liste des rôles exclus
     *
     * @param string $role  Rôle de l'user connecté
     * @param Objet  $objet L'entitée Objet
     *
     * @return boolean
     */
    public function checkAccessToObjet($role, $objet)
    {
        //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
        if(is_null($objet))
        {
            $this->_session->getFlashBag()->add('danger', 'Vous tentez de rejoindre une page qui n\'existe plus.' );
            return false;
        }
        $roles = $objet->getRoles();
        foreach($roles as $restrictedRole){
            //on "break" en retournant null, l'objet n'est pas ajouté
            if( $restrictedRole->getRole() == $role)
                return false;
        }

        return true;
    }
    
    /**
     * Formatte les types d'objet sous forme d'une chaine de caractère avec le séparateur $sep
     *
     * @param array  $types Types de l'objet
     * @param string $sep   Séparateur pour l'implode
     *
     * @return string
     */
    public function formatteTypes( $types, $sep = ' ♦ ' )
    {
        $tabType  = array();
        foreach ($types as $type)
            $tabType[] = $type->getLibelle();

        return implode($sep, $tabType);
    }

    /**
     * [testAliasExist description]
     *
     * @param  [type] $objet [description]
     * @param  [type] $new   [description]
     *
     * @return [type]
     */
    public function testAliasExist( $objet, $new )
    {
        $alias = $this->findOneBy( array( 'alias'=>$objet->getAlias() ) );

        if( $alias && $new === true )
            return true;
        elseif( $alias && $new === false && $alias->getId() != $objet->getId() )
            return true;
        
        return false;
    }

    /**
     * Retourne l'arbo Objets -> contenus
     *
     * @return array
     */
    public function getObjetsAndContenuArbo( $types = null )
    {
        //get objets and IDS
        $objets = is_null($types) ? $this->findAll() : $this->getObjetsByTypes( $types );
        $ids    = array();
        foreach( $objets as $one )
            $ids[] = $one->getId();

        //get Contenus
        $datas    = $this->_contenuManager->getArboForObjet($ids);
        $contenus = array();
        foreach( $datas as $one ) {
            if( $one->objet != null )
                $contenus[ $one->objet ][] = $one;
        }

        //formate datas
        foreach( $objets as $one ) 
        {
            //Traitement pour Article
            if($one->isArticle())
            {
                $results[] = array(
                        "text"  => $one->getTitre(),
                        "value" => "ARTICLE:" . $one->getId()
                );
            }
            //Traitement pour Publication et Infradoc
            else 
            {
                $results[] = array(
                        "text"  => $one->getTitre(),
                        "value" => "PUBLICATION:" . $one->getId()
                );
                
                if( !isset($contenus[ $one->getId() ]) || count( $contenus[ $one->getId() ] ) <= 0 )
                    continue;
                
                foreach( $contenus[ $one->getId() ] as $content ){
                    $results[] = array(
                            "text"  => "|--" . $content->titre,
                            "value" => "INFRADOC:" . $content->id
                    );
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
     * Récupération du nombre de vue total de toutes les publications
     *
     * @return int
     */
    public function getNbVuesPublication()
    {
        return $this->getRepository()->getNbVuesPublication()->getQuery()->getSingleScalarResult();
    }

    /**
     * Retorune l'arbo des articles
     *
     * @return array
     */
    public function getArticlesArbo( $types )
    {
        //get objets
        $objets = $this->getObjetsByTypes( $types );

        //formate datas
        foreach( $objets as $one ) {
            $results[] = array(
                "text" => $one->getTitre(), "value" => "ARTICLE:" . $one->getId()
            );
        }

        return $results;
    }

    /**
     * Retourne la liste des actualités des catégories passées en paramètre
     *
     * @param array $categories Les catégories
     *
     * @return array
     */
    public function getActualitesByCategorie( $categories, $role, $limit = 0, $order = array( 'champ' => 'obj.dateModification', 'tri' => 'DESC') )
    {
        $articles   = $this->getObjetsByTypes( $categories, $limit, $order );
        $actualites = array();

        foreach($articles as $article) {
            if( $this->checkAccessToObjet($role, $article) ) {
                $actu = new \stdClass;

                $actu->id    = $article->getId();
                $actu->titre = $article->getTitre();
                $actu->alias = $article->getAlias();
                $actu->date  = (is_null($article->getDateModification())) ? $article->getDateCreation() : $article->getDateModification();
                $actu->image = $article->getVignette() ? $article->getVignette() : false;

                //resume
                $tab = explode('<!-- pagebreak -->', $article->getResume());
                $actu->resume = $tab[0];
                $actu->hasPageBreak = strpos($article->getResume(),'<!-- pagebreak -->') !== false;

                //types / catégories
                $types            = $article->getTypes();
                $actu->types      = $this->formatteTypes( $types );
                $actu->categories = $this->getCategorieForUrl( $article->getTypes() );

                $actualites[] = $actu;
            }
        }

        usort($actualites, array($this,"triArrayObjetDateAntichronologique"));

        return $actualites;
    }
    
    /**
     * Retourne les catégories qui ont des articles
     *
     * @param array $allCategories Liste des catégories
     *
     * @return array
     */
    public function getCategoriesWithArticles( $allCategories )
    {
        $categories = array();
        foreach($allCategories as $one) {
            $articles = $this->getObjetsByTypes( array($one) );
            if( count($articles) > 0)
            {
                $categ          = new \stdClass;
                $categ->id      = $one->getId();
                $categ->libelle = $one->getLibelle();

                $categories[$one->getOrder()] = $categ;
            }
        }

        ksort($categories);

        return $categories;
    }

    /**
     * Retourne l'objet article pour la page d'accueil
     *
     * @return stdClass
     */
    public function getArticleHome()
    {
        $article = $this->findOneBy( array('id' => 1) );
        $item    = new \stdClass;
        
        $item->id         = $article->getId();
        $item->titre      = $article->getTitre();
        $item->alias      = $article->getAlias();
        $item->image      = $article->getVignette() ? $article->getVignette() : false;
        $item->categories = $this->getCategorieForUrl( $article->getTypes() );

        //resume
        $tab = explode('<!-- pagebreak -->', $article->getResume());
        $item->resume = $tab[0];
        $item->hasPageBreak = strpos($article->getResume(),'<!-- pagebreak -->') !== false;

        return $item;
    }

    /**
     * Retourne la note de l'objet
     *
     * @param array $references   Tableau des références
     * @param array $ponderations Tableau des pondérations
     *
     * @return integer
     */
    public function getNoteReferencement( $references, $ponderations )
    {
        $note = 0;
        foreach($references as $reference){
            $id = $reference->getReference()->getId();

            if( isset($ponderations[ $id ]) )
            {
                $note += $ponderations[ $id ]['poids'];
            }
        }
        
        return $note;
    }

    /**
     * Formatte les productions pour l'affichage des productions liées
     *
     * @param array $datas Liste des prod liées
     *
     * @return array
     */
    public function formatteProductionsLiees( $datas )
    {
        $productions = array();

        foreach($datas as $one) {
            //explode to get datas
            $tab = explode(':', $one);

            //build new object
            $element       = new \StdClass;
            $element->id   = $tab[1];
            $element->brut = $one;

            //switch Objet / Infra-doc
            if( $tab[0] == 'PUBLICATION' ){
                $objet            = $this->findOneBy( array('id' => $tab[1] ) );
                $element->titre   = $objet->getTitre();
                $element->isObjet = 1;
            }else if( $tab[0] == 'INFRADOC' ){
                $contenu          = $this->_contenuManager->findOneBy( array('id' => $tab[1] ) );
                $element->titre   = '|--' . $contenu->getTitre();
                $element->isObjet = 0;
            }else if( $tab[0] == 'ARTICLE' ){
                $objet            = $this->findOneBy( array('id' => $tab[1] ) );
                $element->titre   = $objet->getTitre();
                $element->isObjet = 1;
            }

            $productions[] = $element;
        }

        return $productions;
    }

    /**
     * Retourne les articles d'une catégorie.
     *
     * @param \HopitalNumerique\ObjetBundle\Manager\Reference $categorie Catégorie
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine  $domaine   Domaine
     * @return array<\HopitalNumerique\ObjetBundle\Entity\Objet> Articles
     */
    public function getArticlesForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getRepository()->getArticlesForCategorie($categorie, $domaine);
    }

    /**
     * Retourne le dernier article d'une catégorie.
     *
     * @param \HopitalNumerique\ObjetBundle\Manager\Reference $categorie Catégorie
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine  $domaine   Domaine
     * @return \HopitalNumerique\ObjetBundle\Entity\Objet Dernier article
     */
    public function getLastArticleForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getRepository()->getLastArticleForCategorie($categorie, $domaine);
    }

    /**
     * Retourne les infradocs d'un domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
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
     * Filtre les reférences en fonction de l'objet passés en paramètre
     *
     * @param [type] $objet      [description]
     * @param [type] $references [description]
     *
     * @return [type]
     */
    private function filtreReferencesByDomaines($objet, $references)
    {
        $referencesIds    = array();
        $domainesObjetIds = array();
        $userConnectedDomaineIds = $this->_userManager->getUserConnected()->getDomainesId();

        //Récupération des id de domaine de l'objet
        foreach ($objet->getDomaines() as $domaine) 
        {
            if(in_array($domaine->getId(), $userConnectedDomaineIds))
            {
                $domainesObjetIds[] = $domaine->getId();
            }
        }

        //Vérifie qu'il y a bien un domaine pour la publication courante
        if(count($domainesObjetIds) !== 0)
        {   
            //Récupération des id des références "stdClass" pour récupérer les entités correspondantes et donc les domaines
            foreach ($references as $reference) 
            {
                $referencesIds[] = $reference->id;
            }

            $referencesByIds = $this->_referenceManager->findBy(array('id'=> $referencesIds));

            //Parcourt la liste des entités de référence
            foreach ($referencesByIds as $reference) 
            {
                if(array_key_exists($reference->getId(), $references))
                {
                    $inArray = false;

                    foreach ($reference->getDomaines() as $domaine) 
                    {
                        if(in_array($domaine->getId(), $domainesObjetIds))
                        {
                            $inArray = true;
                            break;
                        }   
                    }

                    if(!$inArray)
                    {
                        unset($references[$reference->getId()]);
                    }
                }
            }
        }
        //Sinon vide les références, car une publication sans domaine ne peut pas être référencées
        else
        {
            $references = array();
        }

        return $references;
    }

    /**
     * Formatte les types de l'objet pour les URLS (catégorie param)
     *
     * @param array $types Les types de l'objet
     *
     * @return string
     */
    private function getCategorieForUrl( $types )
    {
        $type      = $types[0];
        $categorie = '';

        if( $parent = $type->getParent() )
            $categorie .= $parent->getLibelle().'-';
        $categorie .= $type->getLibelle();

        $tool = new Chaine( $categorie );

        return $tool->minifie();
    }

    /**
     * Ajoute les enfants de $objet dans $return, formatées en fonction de $level
     * 
     * @param array    $return
     * @param stdClass $objet
     * @param integer  $level
     * 
     * @return void
     */
    private function getObjetsChilds( &$return, $objet, $level = 1 )
    {
        if( count($objet->childs) > 0 ){
            foreach( $objet->childs as $child ){
                $texte = str_pad($child->titre, strlen($child->titre) + ($level*3), "|--", STR_PAD_LEFT);
                $return[] = array(
                    "text" => $texte, "value" => "INFRADOC:" . $child->id
                );
                $this->getObjetsChilds($return, $child, $level + 1);
            }
        }
    }

    /**
     * Réarrange les objets pour afficher correctement les types
     *
     * @param array $results Les résultats de la requete
     *
     * @return array
     */
    private function rearangeForTypes( $results )
    {
        $objets  = array();

        foreach($results as $result)
        {
            if( isset( $objets[ $result['id'] ] ) )
                $objets[ $result['id'] ]['types'] .= ', ' . $result['types'];
            else
                $objets[ $result['id'] ] = $result;
        }

        return array_values($objets);
    }
    
    /**
     * [formatReferencesOwn description]
     *
     * @param  [type] $retour [description]
     *
     * @return [type]
     */
    private function formatReferencesOwn( &$retour )
    {
        foreach( $retour as $key => $one ){
            $retour[ $key ]['childs'] = $this->getChilds($retour, $one);
        }
    }
    
    /**
     * [getChilds description]
     *
     * @param  [type] $retour [description]
     * @param  [type] $elem   [description]
     *
     * @return [type]
     */
    private function getChilds(&$retour, $elem)
    {
        if( isset( $elem['childs'] ) && count($elem['childs']) ){
            $childs = array();
            foreach( $elem["childs"] as $key => $one ){
                $childs[ $one ] = $retour[ $one ];
                $petitsEnfants  = $this->getChilds($retour, $childs[ $one ]);
                if( $petitsEnfants ){
                    $childs[ $one ]['childs'] = $petitsEnfants;
                    unset( $retour[ $one ] );
                } else {
                    unset( $retour[ $one ] );
                }
            }
            return $childs;
        } else {
            return false;
        }
    }

    /**
     * Enregistre l'entitée
     *
     * @param Entity|array $entity L'entitée
     *
     * @return empty
     */
    public function save( $entity )
    {

      if( is_array($entity) ){
        foreach( $entity as $one )
          if($one->getAlaune() == 1) {
            $this->setAllAlaUneFalse($one->getId());
          }
          $this->_em->persist( $one );
      }else {

        if($entity->getAlaune() == 1) {
          $this->setAllAlaUneFalse($entity->getId());
        }

        $this->_em->persist( $entity );
      }


      $this->_em->flush();
    }

    /**
     * Set le champ A la une à false pour tous les contenus
     */
    public function setAllAlaUneFalse($id) {
      return $this->getRepository()->setAllAlaUneFalse($id)->getQuery()->getResult();
    }

    /**
     * Retourne l'article à la une
     * @return \HopitalNumerique\ObjetBundle\Entity\Objet[]
     */
    public function getArticleAlaUne() {
      return $this->getRepository()->getArticleAlaUne()->getQuery()->getResult();
    }
    
    /**
     * Retourne les articles du domaine 
     * @return \HopitalNumerique\ObjetBundle\Entity\Objet[]
     */
    public function getObjetByDomaine() {
    	$objets = $this->getRepository()->getObjetByDomaine()->getQuery()->getResult();
    	$ids    = array();
    	foreach( $objets as $one )
    		$ids[] = $one->getId();
    	
    		//get Contenus
    		$datas    = $this->_contenuManager->getArboForObjet($ids);
    		$contenus = array();
    		foreach( $datas as $one ) {
    			if( $one->objet != null )
    				$contenus[ $one->objet ][] = $one;
    		}
    	
    		//formate datas
    		foreach( $objets as $one )
    		{
    			//Traitement pour Article
    			if($one->isArticle())
    			{
    				$results[] = array(
    						"text"  => $one->getTitre(),
    						"value" => "ARTICLE:" . $one->getId()
    				);
    			}
    			//Traitement pour Publication et Infradoc
    			else
    			{
    				$results[] = array(
    						"text"  => $one->getTitre(),
    						"value" => "PUBLICATION:" . $one->getId()
    				);
    	
    				if( !isset($contenus[ $one->getId() ]) || count( $contenus[ $one->getId() ] ) <= 0 )
    					continue;
    	
    					foreach( $contenus[ $one->getId() ] as $content ){
    						$results[] = array(
    								"text"  => "|--" . $content->titre,
    								"value" => "INFRADOC:" . $content->id
    						);
    						$this->getObjetsChilds($results, $content, 2);
    					}
    			}
    	
    		}
    	
    		return $results;
    }
}
