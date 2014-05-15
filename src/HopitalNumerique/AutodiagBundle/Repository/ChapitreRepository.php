<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ChapitreRepository
 */
class ChapitreRepository extends EntityRepository
{
    /**
     * Compte le nombre de chapitres lié à loutil
     *
     * @param Outil $outil Outil
     *
     * @return QueryBuilder
     */
    public function countChapitres( $outil )
    {
        return $this->_em->createQueryBuilder()
                         ->select('count(cha)')
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Chapitre', 'cha')
                         ->andWhere('cha.outil = :outil', 'cha.parent IS NULL')
                         ->setParameter('outil', $outil );
    }

    /**
     * Retourne tous les chapitres de l'outil
     *
     * @param Outil $outil L'outil concerné
     *
     * @return QueryBuilder
     */
    public function getArbo( $outil )
    {
        return $this->_em->createQueryBuilder()
                        ->select('cha')
                        ->from('\HopitalNumerique\AutodiagBundle\Entity\Chapitre', 'cha')
                        ->andWhere('cha.outil = :outil')
                        ->orderBy('cha.parent, cha.order')
                        ->setParameter('outil', $outil);
    }
}
