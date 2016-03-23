<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager de l'entité Requete.
 */
class ExpBesoinReponsesManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses';
    protected $_userManager;
    protected $_referenceManager;

    /**
     * Constructeur du manager gérant les chapitres d'outil.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager, ReferenceManager $referenceManager)
    {
        parent::__construct($entityManager);
        $this->_userManager      = $userManager;
        $this->_referenceManager = $referenceManager;
    }

    public function countReponses($expBesoin)
    {
        return $this->getRepository()->countReponses($expBesoin)->getQuery()->getSingleScalarResult();
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param expBesoinReponse $expBesoinReponse      expBesoinReponse concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($expBesoinReponses, $references)
    {
        $selectedReferences = $expBesoinReponses->getReferences();

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

        $references = $this->filtreReferencesByDomaines($expBesoinReponses->getQuestion()->getExpBesoinGestion(), $references);
        
        return $references;
    }

    /**
     * Met à jour l'ordre des chapitres de manière récursive
     *
     * @param array  $elements Les éléments
     * @param Object $parent   L'élément parent | null
     *
     * @return empty
     */
    public function reorder( $elements, $parent )
    {
        $order = 1;

        foreach($elements as $element) 
        {
            $reponse = $this->findOneBy( array('id' => $element['id']) );
            $reponse->setOrder( $order );
            $order++;
        }
    }

    /**
     * Récupère l'ensemble des réponses dans un tableau avec comme clé l'id de la réponse
     *
     * @return array[ExpBesoinReponses] $expBesoinReponses
     * 
     */
    public function getAllReponsesInArrayById()
    {
        $resultats         = array();
        $expBesoinReponses = $this->findBy(array(), array('order' => 'ASC'));

        foreach ($expBesoinReponses as $expBesoinReponse)
        {
            $resultats[$expBesoinReponse->getId()]                  = array();
            $resultats[$expBesoinReponse->getId()]['libelle']       = $expBesoinReponse->getLibelle();
            $resultats[$expBesoinReponse->getId()]['autreQuestion'] = $expBesoinReponse->isAutreQuestion();
            if(!is_null($expBesoinReponse->getRedirigeQuestion()))
            {
                $resultats[$expBesoinReponse->getId()]['idQuestion'] = $expBesoinReponse->getRedirigeQuestion()->getId();
            }
            //$resultats[$expBesoinReponse->getId()]['reference'] = $expBesoinReponse->getReferences();
        }

        return json_encode($resultats);
    }







    /**
     * Filtre les reférences en fonction de l'expBesoinGestion passés en paramètre
     *
     * @param [type] $expBesoinGestion      [description]
     * @param [type] $references [description]
     *
     * @return [type]
     */
    private function filtreReferencesByDomaines($expBesoinGestion, $references)
    {
        $referencesIds    = array();
        $domainesExpBesoinGestionIds = array();
        $userConnectedDomaineIds = $this->_userManager->getUserConnected()->getDomainesId();

        //Récupération des id de domaine de l'expBesoinGestion
        foreach ($expBesoinGestion->getDomaines() as $domaine) 
        {
            if(in_array($domaine->getId(), $userConnectedDomaineIds))
            {
                $domainesExpBesoinGestionIds[] = $domaine->getId();
            }
        }

        //Vérifie qu'il y a bien un domaine pour la publication courante
        if(count($domainesExpBesoinGestionIds) !== 0)
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
                        if(in_array($domaine->getId(), $domainesExpBesoinGestionIds))
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

}