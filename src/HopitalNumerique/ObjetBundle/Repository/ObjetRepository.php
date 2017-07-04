<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * ObjetRepository.
 */
class ObjetRepository extends EntityRepository
{

    /**
     * @param integer $objectId
     *
     * @return Objet
     */
    public function findByIdWithJoin($objectId)
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.domaines', 'd')->addSelect('d')

            ->andWhere('o.id = :objectId')->setParameter('objectId', $objectId)

            ->setMaxResults(1)
            ->getQuery()->getSingleResult()
        ;
    }

    /**
     * @param $ids
     *
     * @return array
     */
    public function findByIdsWithJoin($ids)
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.domaines', 'd')->addSelect('d')

            ->andWhere('o.id IN (:objectIds)')->setParameter('objectIds', $ids)

            ->getQuery()->getResult()
        ;
    }

    /**
     * Returns the list of objects corresponding to ids.
     *
     * @param $ids
     *
     * @return array
     */
    public function findByIds($ids)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('object')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'object')
            ->where('object.id IN (:ids)')
            ->setParameter('ids', $ids)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Objet $objet
     *
     * @return Objet[]
     */
    public function getProductionsLiees(Objet $objet)
    {
        return $this->createQueryBuilder('o')
            ->orWhere('o.objets LIKE :objetId')
            ->setParameter('objetId', sprintf('%%PUBLICATION:%d"%%', $objet->getId()))
            ->orWhere('o.objets LIKE :objetId2')
            ->setParameter('objetId2', sprintf('%%ARTICLE:%d"%%', $objet->getId()))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param array  $domainesIds Tableau d'id de domaines autorisés pour l'utilisateur connecté
     * @param [type] $condition   [description]
     *
     * @return QueryBuilder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select(
                'obj',
                'refEtat',
                'refTypes',
                'cibleDiffusion',
                'user',
                'notes',
                'domaine'
            )
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.etat', 'refEtat')
            ->leftJoin('obj.types', 'refTypes')
            ->leftJoin('obj.cibleDiffusion', 'cibleDiffusion')
            ->leftJoin('obj.lockedBy', 'user')
            ->leftJoin('obj.listeNotes', 'notes')
            ->leftJoin('obj.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            ->orderBy('obj.dateCreation', 'DESC')
        ;

        return $qb;
    }

    /**
     * Retourne la liste des objets.
     *
     * @param array $domains
     *
     * @return QueryBuilder
     */
    public function getObjets($domains = [])
    {
        $qb = $this->createQueryBuilder('obj');
        $qb
            ->select('obj', 'contenus')
            ->leftJoin('obj.contenus', 'contenus')
            ->join('obj.etat', 'etat')
            ->orderBy('obj.titre', 'ASC')
        ;

        if (count($domains) > 0) {
            $qb
                ->leftJoin('obj.domaines', 'domaines')
                ->andWhere('domaines IN (:domains)')
                ->setParameter('domains', $domains)
            ;
        }

        return $qb;
    }

    /**
     * Récupère les objets pour l'export.
     *
     * @param $ids
     *
     * @return QueryBuilder
     */
    public function getDatasForExport($ids)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->where('obj.id IN (:ids)')
            ->orderBy('obj.titre', 'ASC')
            ->setParameter('ids', $ids);

        return $qb;
    }

    /**
     * Récupération du nombre de vue total de toutes les publications.
     *
     * @return QueryBuilder
     */
    public function getNbVuesPublication()
    {
        return $this->_em->createQueryBuilder()
                         ->select('sum(obj.nbVue)')
                         ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
                         ->andWhere('obj.isArticle = 0');
    }

    /**
     * Récupère les données du grid pour un ambassadeur sous forme de tableau correctement formaté.
     *
     * @param $idAmbassadeur
     *
     * @return QueryBuilder
     */
    public function getDatasForGridAmbassadeur($idAmbassadeur)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj.id, obj.titre, refTypes.libelle as types, refUser.id as user')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types', 'refTypes')
            ->leftJoin('obj.ambassadeurs', 'refUser')
            ->where('refUser.id = :idAmbassadeur')
            ->setParameter('idAmbassadeur', $idAmbassadeur->value);

        return $qb;
    }

    /**
     * Récupère les objets pour le flux RSS.
     *
     * @param Domaine $domaine
     *
     * @return QueryBuilder
     */
    public function getObjetsForRSS(Domaine $domaine)
    {
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->innerJoin('obj.domaines', 'domaine', Expr\Join::WITH, 'domaine.id = :domaine')
            ->leftJoin('obj.roles', 'role')
            ->where('obj.etat = :idEtat')
            ->andWhere($qb->expr()->isNull('role.id'))
            ->leftJoin('obj.types', 'refType')
            ->leftJoin('refType.codes', 'codes')
            ->leftJoin('refType.parents', 'typeParent')
            ->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->andX(
                        $qb->expr()->isNotNull('typeParent.id'),
                        $qb->expr()->eq('codes.label', ':code_artcle')
                    ),
                    $qb->expr()->eq('codes.label', ':code_objet')
                )
            )
            ->setParameters([
                'domaine' => $domaine,
                'idEtat' => 3,
                'code_artcle' => 'CATEGORIE_ARTICLE',
                'code_objet' => 'CATEGORIE_OBJET',
            ])
            ->orderBy('obj.dateCreation', 'DESC')
            ->setMaxResults(20)
        ;

        return $qb;
    }

    /**
     * Récupère les objets pour un ambassadeur passé en param.
     *
     * @param $idAmbassadeur
     *
     * @return QueryBuilder
     */
    public function getObjetsByAmbassadeur($idAmbassadeur)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types', 'refTypes')
            ->leftJoin('obj.ambassadeurs', 'refUser')
            ->where('refUser.id = :idAmbassadeur')
            ->setParameter('idAmbassadeur', $idAmbassadeur);

        return $qb;
    }

    /**
     * Retourne la liste des objets selon le/les types.
     *
     * @param array $types Les types à filtrer
     * @param int   $limit
     * @param       $order
     *
     * @param int   $limit
     * @param       $order
     * @param array $domains
     *
     * @return QueryBuilder
     */
    public function getObjetsByTypes($types, $limit = 0, $order, $domains = [])
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types', 'refTypes')
            ->where('refTypes.id IN (:types)', 'obj.etat = 3')
            ->orderBy($order['champ'], $order['tri'])
            ->setParameter('types', $types);

        if ($limit !== 0) {
            $qb->setMaxResults($limit);
        }

        if (count($domains) > 0) {
            $qb
                ->leftJoin('obj.domaines', 'domaines')
                ->andWhere('domaines IN (:domains)')
                ->setParameter('domains', $domains)
            ;
        }

        return $qb;
    }

    /**
     * Retourne la liste des objets selon le/les types et trié par nombre de vues.
     *
     * @param int $limit
     *
     * @return QueryBuilder
     */
    public function getObjetsByNbVue($limit = 0)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types', 'refTypes')
            ->where('obj.etat = 3')
            ->andWhere('obj.publicationPlusConsulte = :true')
            ->setParameters([
                    'true' => true,
            ])
            ->orderBy('obj.nbVue', 'DESC')
        ;

        if ($limit !== 0) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Retourne l'ensemble des productions actives.
     */
    public function getProductionsActive()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types', 'refTypes')
            ->innerJoin('refTypes.parents', 'refTypesParent')
            ->innerJoin('obj.etat', 'etat')
            ->where('refTypesParent.id = :idParent')
            ->setParameter('idParent', 175)
            ->andWhere('etat.id = :idActif')
            ->setParameter('idActif', 3)
            ->orderBy('obj.alias', 'ASC')
        ;

        return $qb;
    }

    /**
     * Retourne l'ensemble des productions actives.
     * @param Domaine[] $domains
     * @return array
     */
    public function getObjetsForDashboard($domains)
    {
        if (empty($domains)) {
            return null;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj.id, obj.nbVue, obj.titre, refType.id as typeId, parentType.id as parentId, refEtat.id as etat, obj.dateCreation')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->innerJoin('obj.types', 'refType')
            ->leftJoin('obj.etat', 'refEtat')
            ->leftJoin('refType.parents', 'parentType')
            ->join(
                'obj.domaines',
                'domaine',
                Expr\Join::WITH,
                $qb->expr()->in(
                    'domaine',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
            ->groupBy('obj.id')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Set toute la colone A la une à false.
     */
    public function setAllAlaUneFalse($id)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->update()
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->set('obj.alaune', '0')
            ->where('obj.id != :id')
            ->setParameter('id', $id)
        ;

        return $qb;
    }

    /**
     * Retourne l'article à la une.
     */
    public function getArticleAlaUne()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->where('obj.alaune = 1')
        ;

        return $qb;
    }

    /**
     * Retourne les articles d'une catégorie.
     *
     *
     * @param Reference $categorie
     * @param Domaine   $domaine
     *
     * @return Objet[] Articles
     */
    public function getArticlesForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getArticlesForCategorieQueryBuilder($categorie, $domaine)->getQuery()->getResult();
    }

    /**
     * Retourne les articles d'une catégorie.
     *
     * @param Reference $categorie
     * @param Domaine   $domaine
     *
     * @return QueryBuilder
     */
    public function getArticlesForCategorieQueryBuilder(Reference $categorie, Domaine $domaine)
    {
        $query = $this->createQueryBuilder('article');

        $query
            ->andWhere('article.isArticle = :isArticle')
            ->setParameter('isArticle', true)
            ->innerJoin('article.domaines', 'domaine', Expr\Join::WITH, 'domaine = :domaine')
            ->setParameter('domaine', $domaine)
            ->innerJoin('article.types', 'categorie')
            ->innerJoin('categorie.parents', 'categorieParent')
            ->andWhere($query->expr()->orX('categorie = :categorie', 'categorieParent = :categorie'))
            ->setParameter('categorie', $categorie)
            ->addOrderBy('article.dateCreation', 'DESC')
            ->addOrderBy('article.id', 'DESC')
        ;

        return $query;
    }

    /**
     * @param Reference $categorie
     * @param Domaine   $domaine
     *
     * @return Objet
     */
    public function getLastArticleForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getArticlesForCategorieQueryBuilder($categorie, $domaine)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Retourne les objets du domaine.
     *
     * @return QueryBuilder
     */
    public function getObjetByDomaine()
    {
        /** @var Domaine $domaine */
        $domaine = $this
            ->getEntityManager()
            ->getRepository('HopitalNumeriqueDomaineBundle:Domaine')
            ->getDomaineFromHttpHost($_SERVER['SERVER_NAME'])
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->innerJoin('obj.types', 'refType')
            ->leftJoin('obj.etat', 'refEtat')
            ->leftJoin('refType.parents', 'parentType')
            ->leftJoin('obj.domaines', 'domaine')
            ->where('domaine.id = :idDomaine')
            ->setParameter('idDomaine', ($domaine) ? $domaine->getId() : 1)
        ;

        return $qb;
    }

    /**
     * Retourne les infradocs d'un domaine.
     *
     * @param Domaine $domaine
     *
     * @return Objet[]
     */
    public function getInfradocs(Domaine $domaine)
    {
        $qb = $this->createQueryBuilder('objet');

        $qb
            ->innerJoin('objet.domaines', 'domaine', Expr\Join::WITH, $qb->expr()->eq('domaine.id', ':domaine'))
            ->where($qb->expr()->eq('objet.isInfraDoc', ':infradoc'))
            ->orderBy('objet.titre', 'ASC')
            ->setParameters([
                'domaine' => $domaine,
                'infradoc' => true,
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les publications par domaine et compétences de l'ambassadeur.
     *
     * @param $types
     * @param $id
     * @param $domaines
     *
     * @return Objet[]
     */
    public function getObjetsByTypeAmbassadeursAndDomaines($types, $id, $domaines)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('obj.id, obj.titre, ambassadeur.id as amb_id')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types', 'refTypes')
            ->Join('obj.domaines', 'dom', Expr\Join::WITH, $qb->expr()->in('dom', ':domaine'))
            ->leftJoin('obj.ambassadeurs', 'ambassadeur')->where('refTypes.id IN (:types)', 'obj.etat = 3')
            ->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->isNull('obj.dateDebutPublication'),
                    $qb->expr()->lte('obj.dateDebutPublication', ':today')
                ),
                $qb->expr()->orx(
                    $qb->expr()->isNull('obj.dateFinPublication'),
                    $qb->expr()->gte('obj.dateFinPublication', ':today')
                )
            )
            ->groupBy('obj.id')
            ->having('ambassadeur.id != :id')
            ->setParameter('id', $id)
            ->setParameter('domaine', $domaines)
            ->setParameter('today', new \DateTime())
            ->setParameter('types', $types);

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Retourne les publications par domaine et type.
     *
     * @param $types
     * @param $domaine
     *
     * @return Objet[]
     */
    public function getObjetsByTypesAndDomaine($types, $domaine)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types', 'refTypes')
            ->leftJoin('obj.domaines', 'dom')
            ->where('refTypes.id IN (:types)', 'obj.etat = 3')
            ->groupBy('obj.id')
            ->andWhere('dom.id = :idDomaine')
            ->setParameter('idDomaine', $domaine)
            ->setParameter('types', $types)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the 'obj_id' and 'obj_objets' columns for all objects
     *
     * @return array
     */
    public function getObjectRelationships()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('object.id, object.objets')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'object')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $mandatoryReferencesId
     * @param array $optionalReferencesId
     * @param int $entityType
     *
     * @return Objet[]
     */
    public function getObjectForReferences($mandatoryReferencesId = [], $optionalReferencesId = [], $entityType = Entity::ENTITY_TYPE_OBJET)
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->setParameter('entityType', $entityType)
        ;

        foreach ($mandatoryReferencesId as $k => $referenceId) {
            $queryBuilder
                ->join(
                    EntityHasReference::class,
                    sprintf('ehr%d', $k),
                    Expr\Join::WITH,
                    sprintf('ehr%d.entityId = o.id AND ehr%d.entityType = :entityType', $k, $k)
                )
                ->join(
                    sprintf('ehr%d.reference', $k),
                    sprintf('r%d', $k),
                    Expr\Join::WITH,
                    sprintf('r%d.id = :referenceId%d', $k, $k)
                )
                ->setParameter(sprintf('referenceId%d', $k), $referenceId)
            ;
        }

        if (count($optionalReferencesId)) {
            $queryBuilder
                ->join(EntityHasReference::class, 'ehr', Expr\Join::WITH, 'ehr.entityId = o.id AND ehr.entityType = :entityType')
                ->join('ehr.reference', 'r', Expr\Join::WITH, 'r.id IN (:referencesId)')
                ->setParameter('referencesId', $optionalReferencesId)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUpdatedObjectsSinceLastView(User $user)
    {
        return $this->createQueryBuilder('o')
            ->join('o.consultations', 'c', Expr\Join::WITH, 'c.user = :userId AND o.dateModification > c.dateLastConsulted')
            ->setParameter('userId', $user->getId())

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getMostViewedObjectsForUser(User $user)
    {
        return $this->createQueryBuilder('o')
            ->join('o.consultations', 'c', Expr\Join::WITH, 'c.user = :userId AND c.contenu IS NULL')
            ->addSelect('c')
            ->setParameter('userId', $user->getId())

            ->orderBy('c.viewsCount', 'DESC')

            ->setMaxResults(5)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getLastViewedObjects(User $user)
    {
        return $this->createQueryBuilder('o')
            ->join('o.consultations', 'c', Expr\Join::WITH, 'c.user = :userId')
            ->setParameter('userId', $user->getId())

            ->orderBy('c.dateLastConsulted', 'DESC')

            ->setMaxResults(5)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param User           $user
     * @param Domaine[]|null $domains
     *
     * @return array
     */
    public function getViewedObjects(User $user, $domains = null)
    {
        $qb = $this->createQueryBuilder('object')
            ->addSelect('consultations')
            ->join('object.consultations', 'consultations', Expr\Join::WITH, 'consultations.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        if (null !== $domains) {
            $qb->join('consultations.domaine', 'domain', Expr\Join::WITH, 'domain.id IN (:domains)')
                ->setParameter('domains', $domains)
            ;
        }

        return $qb
            ->orderBy('consultations.dateLastConsulted', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
