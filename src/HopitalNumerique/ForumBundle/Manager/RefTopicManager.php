<?php

namespace HopitalNumerique\ForumBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité RefObjet.
 */
class RefTopicManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ForumBundle\Entity\RefTopic';

    /**
     * Retourne la liste des références topics pour la recherche
     *
     * @param array $references Liste des références
     *
     * @return array
     */
    public function getTopicForRecherche( $references )
    {
        //return $this->getRepository()->getTopicForRecherche( $references )->getQuery()->getResult();
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('refO')
                        ->from('\HopitalNumerique\ForumBundle\Entity\RefTopic', 'refO')
                        ->leftJoin('refO.topic','top')
                        ->andWhere('refO.reference in (:references)','top.isClosed = false')
                        ->setParameter('references', $references )
                        ->orderBy('refO.primary', 'ASC')
                        ->getQuery()
                        ->getResult();
    }
}