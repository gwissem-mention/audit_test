<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ReponseRepository
 */
class ReponseRepository extends EntityRepository
{
    /**
     * Retourne la liste des questions dans le bon ordre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return integer
     */
    public function getReponsesByResultats( $resultatIds )
    {
        return $this->_em->createQueryBuilder()
                         ->select('rep.id, rep.value, rep.remarque, res.id as resId, que.id as queId')
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Reponse', 'rep')
                         ->leftJoin('rep.question', 'que')
                         ->leftJoin('rep.resultat', 'res')
                         ->andWhere('res.id IN (:resultatIds)')
                         ->setParameter('resultatIds', $resultatIds )
                         ->orderBy('res.id', 'ASC');
    }
}
