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
use Nodevo\RoleBundle\Entity\Role;

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
     * @param array                                          $groupedReferences      Références
     * @param array<integer>|null                            $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null                            $publicationCategoryIds ID des catégories de publications à récupérer
     * @return array EntitiesHasReference
     */
    public function getWithNotes(Domaine $domaine, array $groupedReferences, array $entityTypeIds = null, array $publicationCategoryIds = null)
    {
        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $qb = $this->createQueryBuilder('entityHasReference');

        $qb
            ->select(
                'entityHasReference.entityType',
                'entityHasReference.entityId',
                'COUNT(DISTINCT(entityHasReference.reference)) AS referencesCount',
                'SUM(DISTINCT(entityHasReference.primary)) AS primarySum',
                'entityHasNote.note',
                'objetPointDurType.id as objetPointDurTypeId',
                'GROUP_CONCAT(objetRole.id) AS objetRoleIds',
                'GROUP_CONCAT(contenuObjetRole.id) AS contenuObjetRoleIds'
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

        // ET pour chaque référence de niveau 1 et OU pour les sous-références
        for ($i = 0; $i < count($groupedReferences); $i++) {
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

        $qb
            //<-- Objets
            ->leftJoin(
                Objet::class,
                'objet',
                Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('entityHasReference.entityType', ':entityTypeObjet'),
                    $qb->expr()->eq('objet.id', 'entityHasReference.entityId'),
                    $qb->expr()->orX($qb->expr()->isNull('objet.dateDebutPublication'), $qb->expr()->gte('objet.dateDebutPublication', ':now')),
                    $qb->expr()->orX($qb->expr()->isNull('objet.dateFinPublication'), $qb->expr()->lt('objet.dateFinPublication', ':now'))
                )
            )
            ->setParameter('entityTypeObjet', Entity::ENTITY_TYPE_OBJET)
            ->leftJoin(
                'objet.roles',
                'objetRole'
            )
        ;
        $qb
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
                'contenuObjet'
            )
        ;
        $qb
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

        return $qb->getQuery()->getResult();
    }
}
