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
     * Retourne les topics d'un forum.
     *
     * @param \HopitalNumerique\ForumBundle\Entity\Forum $forum Forum
     * @return array<\HopitalNumerique\ForumBundle\Entity\Topic> Topics
     */
    public function findByForum(Forum $forum)
    {
        return $this->getRepository()->findByForum($forum);
    }
}