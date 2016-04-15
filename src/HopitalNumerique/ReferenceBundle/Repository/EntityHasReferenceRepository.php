<?php
namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * EntityHasReferenceRepository.
 */
class EntityHasReferenceRepository extends EntityRepository
{
    /**
     * Retourne les EntityHasReference par type d'entité, ID d'entité et domaines.
     *
     * @param integer $entityType Type d'entité
     * @param integer $entityId ID de l'entité
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\EntityHasReference> EntitiesHasReference
     */
    public function findByEntityTypeAndEntityIdAndDomaines($entityType, $entityId, array $domaines)
    {
        if (0 === count($domaines)) {
            return [];
        }

        $qb = $this->createQueryBuilder('entityHasReference');
        $qb
            ->innerJoin('entityHasReference.reference', 'reference')
            ->innerJoin('reference.domaines', 'domaine', Expr\Join::WITH, $qb->expr()->in('domaine', ':domaines'))
            ->where(
                $qb->expr()->eq('entityHasReference.entityType', ':entityType'),
                $qb->expr()->eq('entityHasReference.entityId', ':entityId')
            )
            ->setParameters([
                'entityType' => $entityType,
                'entityId' => $entityId,
                'domaines' => $domaines
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
