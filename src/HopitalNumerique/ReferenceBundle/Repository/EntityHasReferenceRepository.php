<?php
namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasNote;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

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

    /**
     * Retourne toutes les entités (couples type + id).
     *
     * @return array Entités
     */
    public function getAllDistinctEntityTypesAndIds()
    {
        $qb = $this->createQueryBuilder('entityHasReference');

        $qb
            ->select('entityHasReference.entityType, entityHasReference.entityId')
            ->groupBy('entityHasReference.entityType')
            ->addGroupBy('entityHasReference.entityId')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les EntityHasReference avec leur note.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param array $references Références
     * @return array EntitiesHasReference
     */
    public function getWithNotes(Domaine $domaine, array $references)
    {
        $qb = $this->createQueryBuilder('entityHasReference');

        $qb
            ->select('entityHasReference.entityType', 'entityHasReference.entityId', 'entityHasReference.primary', 'entityHasNote.note', 'objetPointDurType.id as objetPointDurTypeId')
            ->leftJoin(
                EntityHasNote::class,
                'entityHasNote',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', 'entityHasNote.entityType'),
                    $qb->expr()->eq('entityHasReference.entityId', 'entityHasNote.entityId'),
                    $qb->expr()->eq('entityHasNote.domaine', ':domaine')
                )
            )
            ->leftJoin(
                Objet::class,
                'objetPointDur',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('objetPointDur.id', 'entityHasReference.entityId'),
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeObjet')
                )
            )
            ->leftJoin(
                'objetPointDur.types',
                'objetPointDurType',
                Expr\Join::WITH,
                $qb->expr()->eq('objetPointDurType.id', ':objetCategoriePointDur')
            )
            ->where($qb->expr()->in('entityHasReference.reference', ':references'))
            ->setParameters([
                'domaine' => $domaine,
                'references' => $references,
                'entityTypeObjet' => Entity::ENTITY_TYPE_OBJET,
                'objetCategoriePointDur' => Reference::CATEGORIE_OBJET_POINT_DUR_ID
            ])
            ->groupBy('entityHasReference.entityType', 'entityHasReference.entityId')
        ;

        return $qb->getQuery()->getResult();
    }
}
