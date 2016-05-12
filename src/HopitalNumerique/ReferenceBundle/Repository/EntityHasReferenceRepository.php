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
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
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
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine                Domaine
     * @param array|null                                     $groupedReferences      Références
     * @param array<integer>|null                            $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null                            $publicationCategoryIds ID des catégories de publications à récupérer
     * @param array                                          $resultFilters          Filtres à appliquer
     * @return array EntitiesHasReference
     */
    public function getWithNotes(Domaine $domaine, array $groupedReferences = null, array $entityTypeIds = null, array $publicationCategoryIds = null, $resultFilters = [])
    {
        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $qb = $this->createQueryBuilder('entityHasReference');
        $referenceIds = [];

        $qb
            ->select(
                'entityHasReference.entityType',
                'entityHasReference.entityId',
                'COUNT(DISTINCT(entityHasReferenceMatch.reference)) AS referencesCount',
                'SUM(DISTINCT(entityHasReferenceMatch.primary)) AS primarySum',
                'entityHasNote.note',
                'objetPointDurType.id as objetPointDurTypeId',
                'GROUP_CONCAT(objetRole.id) AS objetRoleIds',
                'GROUP_CONCAT(contenuObjetRole.id) AS contenuObjetRoleIds',
                'GROUP_CONCAT(objetType.id) AS objetTypeIds',
                'GROUP_CONCAT(contenuObjetType.id) AS contenuObjetTypeIds',
                'objet.id as objetId'
            )
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
        ;

        if (null !== $groupedReferences) {
            // ET pour chaque référence de niveau 1 et OU pour les sous-références
            for ($i = 0; $i < count($groupedReferences); $i++) {
                $referenceIds = array_merge($referenceIds, $groupedReferences[$i]);
                $qb
                    ->innerJoin(
                        EntityHasReference::class,
                        'entityHasReference'.$i,
                        Expr\Join::WITH,
                        $qb->expr()->andX(
                            $qb->expr()->eq('entityHasReference.entityType', 'entityHasReference'.$i.'.entityType'),
                            $qb->expr()->eq('entityHasReference.entityId', 'entityHasReference'.$i.'.entityId'),
                            $qb->expr()->in('entityHasReference'.$i.'.reference', ':references'.$i)
                        )
                    )
                    ->setParameter(':references'.$i, $groupedReferences[$i])
                ;
            }
        }

        //<-- Références matchées
        $qb
            ->leftJoin(
                EntityHasReference::class,
                'entityHasReferenceMatch',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', 'entityHasReferenceMatch.entityType'),
                    $qb->expr()->eq('entityHasReference.entityId', 'entityHasReferenceMatch.entityId'),
                    $qb->expr()->in('entityHasReferenceMatch.reference', (count($referenceIds) > 0 ? $referenceIds : [0]))
                )
            )
        ;
        //-->

        $qb
            //<-- Objets
            ->leftJoin(
                Objet::class,
                'objet',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeObjet'),
                    $qb->expr()->eq('objet.id', 'entityHasReference.entityId'),
                    $qb->expr()->orX($qb->expr()->isNull('objet.dateDebutPublication'), $qb->expr()->lte('objet.dateDebutPublication', ':now')),
                    $qb->expr()->orX($qb->expr()->isNull('objet.dateFinPublication'), $qb->expr()->gt('objet.dateFinPublication', ':now'))
                )
            )
            ->setParameter('entityTypeObjet', Entity::ENTITY_TYPE_OBJET)
            ->leftJoin(
                'objet.roles',
                'objetRole'
            )
            ->leftJoin(
                'objet.types',
                'objetType'
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
            ->setParameter('objetCategoriePointDur', Reference::CATEGORIE_OBJET_POINT_DUR_ID)
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
            ->setParameter('entityTypeContenu', Entity::ENTITY_TYPE_CONTENU)
            ->leftJoin(
                'contenu.objet',
                'contenuObjet',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->orX($qb->expr()->isNull('contenuObjet.dateDebutPublication'), $qb->expr()->lte('contenuObjet.dateDebutPublication', ':now')),
                    $qb->expr()->orX($qb->expr()->isNull('contenuObjet.dateFinPublication'), $qb->expr()->gt('contenuObjet.dateFinPublication', ':now'))
                )
            )
            ->leftJoin(
                'contenuObjet.types',
                'contenuObjetType'
            )
            ->leftJoin(
                'contenuObjet.roles',
                'contenuObjetRole'
            )
            ->leftJoin(
                'contenu.domaines',
                'contenuDomaine'
            )
            ->leftJoin(
                'contenuObjet.domaines',
                'contenuObjetDomaine'
            )
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('contenu.id'),
                $qb->expr()->eq('contenuDomaine.id', ':domaine'),
                $qb->expr()->andX($qb->expr()->isNull('contenuDomaine.id'), $qb->expr()->eq('contenuObjetDomaine.id', ':domaine'))
            ))
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
            ->setParameter('entityTypeForumTopic', Entity::ENTITY_TYPE_FORUM_TOPIC)
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
            ->setParameter('entityTypeAmbassadeur', Entity::ENTITY_TYPE_AMBASSADEUR)
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
            ->setParameter('entityTypeRechercheParcours', Entity::ENTITY_TYPE_RECHERCHE_PARCOURS)
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
            ->setParameter('entityTypeCommunautePratiqueGroupe', Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE)
            //-->
            ->groupBy('entityHasReference.entityType', 'entityHasReference.entityId')
            ->setParameter('domaine', $domaine)
            ->setParameter('now', $now)
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

        $resultFiltersConditions = [];
        foreach ($resultFilters as $resultType => $resultFilter) {
            if (count($resultFilter) > 0) {
                switch ($resultType) {
                    case 'objetIds':
                        $resultFiltersConditions[] = 'objet.id IN (:objetIds)';// $qb->expr()->in('objet.id', ':objetIds')->setParameter('objetIds', $resultFilter);
                        $qb->setParameter('objetIds', $resultFilter);
                        break;
                    case 'contenuIds':
                        $resultFiltersConditions[] = 'contenu.id IN (:contenuIds)';//$qb->expr()->in('contenu.id', ':contenuIds')->setParameter('contenuIds', $resultFilter);
                        $qb->setParameter('contenuIds', $resultFilter);
                        break;
                }
            }
        }
        if (count($resultFiltersConditions) > 0) {
            $qb
                ->andWhere(implode(' OR ', $resultFiltersConditions))
                //->setParameters($resultFiltersParameters)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
