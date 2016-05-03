<?php

namespace HopitalNumerique\ForumBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ForumBundle\Entity\Forum;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Symfony\Component\Validator\Constraints\Null;

use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Manager de l'entité Topic.
 */
class TopicManager extends BaseManager
{
    protected $_class = '\HopitalNumerique\ForumBundle\Entity\Topic';
    protected $_userManager;
    protected $_domaineManager;
    protected $_referenceManager;

    /**
     * Constructeur du manager gérant les références
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager, DomaineManager $domaineManager, ReferenceManager $referenceManager)
    {
        parent::__construct($entityManager);

        $this->_userManager      = $userManager;
        $this->_domaineManager   = $domaineManager;
        $this->_referenceManager = $referenceManager;
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param topic $topic      topic concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($topic, $references)
    {
        $selectedReferences = $topic->getReferences();

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

        $references = $this->filtreReferencesByDomaines($topic->getBoard()->getCategory()->getForum(), $references);
        
        return $references;
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param topic $topic      topic concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferencesOwn($topic)
    {
        $return = array();
        $selectedReferences = $topic->getReferences();

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
     * Récupère les derniers topics commentés par type de forum
     *
     * @param $id int
     * @param $limit int
     *
     * @return \HopitalNumerique\ForumBundle\Entity\Topic[] Liste des topics
     */
    public function getLastTopicsForum($id, $limit = null) {
      return $this->getRepository()->getLastTopicsForum($id, $limit)->getQuery()->getResult();
    }

    /**
     * Récupère les derniers topics commentés par type de forum et affiche les epinglés en premier
     * si le nombre d'epinglés est inferieur a la limit alors on recupere les non epinglés
     *
     * @param $id int
     * @param $limit int
     * @param $epingle boolean
     *
     * @return \HopitalNumerique\ForumBundle\Entity\Topic[] Liste des topics
     */
    public function getLastTopicsForumEpingle($id, $limit = null) {
    	$topicEpingle = $this->getRepository()->getLastTopicsForumEpingle($id, $limit, true)->getQuery()->getResult();
    	
    	if ($limit > count($topicEpingle)) {
    		$topic = $this->getRepository()->getLastTopicsForumEpingle($id, $limit - count($topicEpingle),false)->getQuery()->getResult();
    		$topicEpingle = array_merge($topicEpingle, $topic);
    	}

    	return $topicEpingle;
    }
    
    /**
     * Retourne les topics d'un forum.
     *
     * @param \HopitalNumerique\ForumBundle\Entity\Forum $forum Forum
     * @return array<\HopitalNumerique\ForumBundle\Entity\Topic> Topics
     */
    public function findByForum(Forum $forum)
    {
        return $this->getRepository()->findByForum($forum);
    }

    /**
     * Filtre les reférences en fonction de l'outil passés en paramètre
     *
     * @param [type] $outil      [description]
     * @param [type] $references [description]
     *
     * @return [type]
     */
    private function filtreReferencesByDomaines($forum, $references)
    {
        $referencesIds    = array();
        $domainesForumIds = array();
        $userConnectedDomaineIds = $this->_userManager->getUserConnected()->getDomainesId();
        $domaines = $this->_domaineManager->getDomaineForForumId($forum->getId());

        //Récupération des id de domaine de l'outil
        foreach ($domaines as $domaine) 
        {
            if(in_array($domaine->getId(), $userConnectedDomaineIds))
            {
                $domainesForumIds[] = $domaine->getId();
            }
        }

        //Vérifie qu'il y a bien un domaine pour la publication courante
        if(count($domainesForumIds) !== 0)
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
                        if(in_array($domaine->getId(), $domainesForumIds))
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
}