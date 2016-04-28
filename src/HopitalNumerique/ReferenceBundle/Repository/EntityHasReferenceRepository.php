<?php
namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
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
     * @param array               $references             Références
     * @param array<integer>|null $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null $publicationCategoryIds ID des catégories de publications à récupérer
     * @return array EntitiesHasReference
     */
    public function getWithNotes(Domaine $domaine, array $references, array $entityTypeIds = null, array $publicationCategoryIds = null)
    {
        $qb = $this->createQueryBuilder('entityHasReference');

        $qb
            ->select('entityHasReference.entityType', 'entityHasReference.entityId', 'COUNT(DISTINCT(entityHasReference.reference)) AS referencesCount', 'SUM(DISTINCT(entityHasReference.primary)) AS primarySum', 'entityHasNote.note', 'objetPointDurType.id as objetPointDurTypeId')
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
            ->groupBy('entityHasReference.entityType', 'entityHasReference.entityId')
            ->setParameters([
                'domaine' => $domaine,
                'references' => $references,
                'entityTypeObjet' => Entity::ENTITY_TYPE_OBJET,
                'objetCategoriePointDur' => Reference::CATEGORIE_OBJET_POINT_DUR_ID
            ])
        ;

        if (null !== $publicationCategoryIds) {
            $qb
                ->leftJoin(
                    Objet::class,
                    'objet',
                    Expr\Join::WITH,
                    $qb->expr()->andX(
                        $qb->expr()->eq('objet.id', 'entityHasReference.entityId'),
                        $qb->expr()->eq('entityHasReference.entityType', ':entityTypeObjet')
                    )
                )
                ->leftJoin(
                    'objet.types',
                    'objetCategory'
                )
                ->leftJoin(
                    Contenu::class,
                    'contenu',
                    Expr\Join::WITH,
                    $qb->expr()->andX(
                        $qb->expr()->eq('contenu.id', 'entityHasReference.entityId'),
                        $qb->expr()->eq('entityHasReference.entityType', ':entityTypeContenu')
                    )
                )
                ->leftJoin(
                    'contenu.types',
                    'contenuCategory'
                )
                ->leftJoin(
                    'contenu.objet',
                    'contenuObjet'
                )
                ->leftJoin(
                    'contenuObjet.types',
                    'contenuObjetCategory'
                )
                ->setParameter('entityTypeContenu', Entity::ENTITY_TYPE_CONTENU)
                ->setParameter('publicationCategoryIds', $publicationCategoryIds)
            ;
            if (null !== $entityTypeIds) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->orX(
                            $qb->expr()->in('objetCategory.id', ':publicationCategoryIds'),
                            $qb->expr()->in('contenuCategory.id', ':publicationCategoryIds'),
                            $qb->expr()->in('contenuObjetCategory.id', ':publicationCategoryIds')
                        ),
                        $qb->expr()->in('entityHasReference.entityType', ':entityTypes')
                    ))
                    ->setParameter('entityTypes', $entityTypeIds)
                ;
            } else {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->in('objetCategory.id', ':publicationCategoryIds'),
                        $qb->expr()->in('contenuCategory.id', ':publicationCategoryIds'),
                        $qb->expr()->in('contenuObjetCategory.id', ':publicationCategoryIds')
                    ))
                ;
            }
        } elseif (null !== $entityTypeIds) {
            $qb
                ->andWhere($qb->expr()->in('entityHasReference.entityType', ':entityTypes'))
                ->setParameter('entityTypes', $entityTypeIds)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
