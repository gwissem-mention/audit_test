<?php
namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasNote;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;

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
            ->where($qb->expr()->in('entityHasReference.reference', ':references'))
            //<-- Objets
            ->leftJoin(
                Objet::class,
                'objet',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeObjet'),
                    $qb->expr()->eq('objet.id', 'entityHasReference.entityId')
                )
            )
            ->leftJoin(
                'objet.domaines',
                'objetDomaine'
            )
            ->andWhere($qb->expr()->orX($qb->expr()->isNull('objet.id'), $qb->expr()->eq('objetDomaine.id', ':domaine')))
            ->leftJoin(
                'objet.types',
                'objetPointDurType',
                Expr\Join::WITH,
                $qb->expr()->eq('objetPointDurType.id', ':objetCategoriePointDur')
            )
            //-->
            //<-- Contenus
            ->leftJoin(
                Contenu::class,
                'contenu',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeContenu'),
                    $qb->expr()->eq('contenu.id', 'entityHasReference.entityId')
                )
            )
            ->leftJoin(
                'contenu.domaines',
                'contenuDomaine'
            )
            ->andWhere($qb->expr()->orX($qb->expr()->isNull('contenu.id'), $qb->expr()->eq('contenuDomaine.id', ':domaine')))
            //-->
            //<-- Fils de forum
            ->leftJoin(
                Topic::class,
                'topic',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeForumTopic'),
                    $qb->expr()->eq('topic.id', 'entityHasReference.entityId'),
                    $qb->expr()->eq($domaine->getId(), Domaine::DOMAINE_HOPITAL_NUMERIQUE_ID)
                )
            )
            //-->
            //<-- Ambassadeurs
            ->leftJoin(
                User::class,
                'ambassadeur',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeAmbassadeur'),
                    $qb->expr()->eq('ambassadeur.id', 'entityHasReference.entityId')
                )
            )
            ->leftJoin(
                'ambassadeur.domaines',
                'ambassadeurDomaine'
            )
            ->andWhere($qb->expr()->orX($qb->expr()->isNull('ambassadeur.id'), $qb->expr()->eq('ambassadeurDomaine.id', ':domaine')))
            //-->
            //<-- Recherche parcours
            ->leftJoin(
                RechercheParcours::class,
                'rechercheParcours',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeRechercheParcours'),
                    $qb->expr()->eq('rechercheParcours.id', 'entityHasReference.entityId')
                )
            )
            ->leftJoin(
                'rechercheParcours.recherchesParcoursGestion',
                'rechercheParcoursGestion'
            )
            ->leftJoin(
                'rechercheParcoursGestion.domaines',
                'rechercheParcoursGestionDomaine'
            )
            ->andWhere($qb->expr()->orX($qb->expr()->isNull('rechercheParcours.id'), $qb->expr()->eq('rechercheParcoursGestionDomaine.id', ':domaine')))
            //-->
            //<-- Groupes de la communauté de pratiques
            ->leftJoin(
                Groupe::class,
                'communautePratiqueGroupe',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeCommunautePratiqueGroupe'),
                    $qb->expr()->eq('communautePratiqueGroupe.id', 'entityHasReference.entityId'),
                    $qb->expr()->eq('communautePratiqueGroupe.domaine', ':domaine')
                )
            )
            //-->
            ->groupBy('entityHasReference.entityType', 'entityHasReference.entityId')
            ->setParameters([
                'domaine' => $domaine,
                'references' => $references,
                'entityTypeObjet' => Entity::ENTITY_TYPE_OBJET,
                'entityTypeContenu' => Entity::ENTITY_TYPE_CONTENU,
                'entityTypeForumTopic' => Entity::ENTITY_TYPE_FORUM_TOPIC,
                'entityTypeAmbassadeur' => Entity::ENTITY_TYPE_AMBASSADEUR,
                'entityTypeRechercheParcours' => Entity::ENTITY_TYPE_RECHERCHE_PARCOURS,
                'entityTypeCommunautePratiqueGroupe' => Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE,
                'objetCategoriePointDur' => Reference::CATEGORIE_OBJET_POINT_DUR_ID
            ])
        ;

        if (null !== $publicationCategoryIds) {
            $qb
                ->leftJoin(
                    'objet.types',
                    'objetCategory'
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
