<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Doctrine\ORM\Query\Expr;

/**
 * ObjetRepository
 */
class ObjetRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @param array $domainesIds Tableau d'id de domaines autorisés pour l'utilisateur connecté
     * @param [type] $condition   [description]
     *
     * @return array
     */
    public function getDatasForGrid( $domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('obj.id, obj.titre, obj.isInfraDoc, obj.nbVue, refEtat.libelle as etat, obj.dateCreation, obj.dateModification, obj.lock, user.email as lockedBy, refTypes.libelle as types, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.etat','refEtat')
            ->leftJoin('obj.types','refTypes')
            ->leftJoin('obj.lockedBy','user')
            ->leftJoin('obj.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            ->groupBy('obj.id')
        ;
            
        return $qb;
    }

    /**
     * Récupère les objets pour l'export
     *
     * @return array
     */
    public function getDatasForExport( $ids )
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
     * Récupération du nombre de vue total de toutes les publications
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
     * Récupère les données du grid pour un ambassadeur sous forme de tableau correctement formaté
     *
     * @return QueryBuilder
     */
    public function getDatasForGridAmbassadeur( $idAmbassadeur )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj.id, obj.titre, refTypes.libelle as types, refUser.id as user')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->leftJoin('obj.ambassadeurs','refUser')
            ->where('refUser.id = :idAmbassadeur')
            ->setParameter('idAmbassadeur', $idAmbassadeur->value );
        
        return $qb;
    }
    
    /**
     * Récupère les objets pour le flux RSS
     *
     * @return QueryBuilder
     */
    public function getObjetsForRSS()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->where('obj.etat = :idEtat')
            ->leftJoin('obj.types','refTypes')
            ->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->andX(
                        $qb->expr()->isNotNull('refTypes.parent'),
                        $qb->expr()->eq('refTypes.code', ':code_artcle')
                    ),
                    $qb->expr()->eq('refTypes.code', ':code_objet')
                )
            )
            ->setParameters(array(
                'idEtat'      => 3,
                'code_artcle' => 'CATEGORIE_ARTICLE',
                'code_objet'  => 'CATEGORIE_OBJET'
            ))
            ->orderBy('obj.dateCreation', 'DESC');
        
        return $qb;
    }
    
    /**
     * Récupère les objets pour un ambassadeur passé en param
     *
     * @return QueryBuilder
     */
    public function getObjetsByAmbassadeur( $idAmbassadeur )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->leftJoin('obj.ambassadeurs','refUser')
            ->where('refUser.id = :idAmbassadeur')
            ->setParameter('idAmbassadeur', $idAmbassadeur );
        
        return $qb;
    }

    /**
     * Retourne la liste des objets selon le/les types
     *
     * @param array $types Les types à filtrer
     *
     * @return QueryBuilder
     */
    public function getObjetsByTypes( $types, $limit = 0, $order )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->where('refTypes.id IN (:types)','obj.etat = 3')
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
            ->setParameter('today', new \DateTime() )
            ->orderBy($order['champ'], $order['tri'])
            ->setParameter('types', $types );
        
        if( $limit !== 0 )
            $qb->setMaxResults($limit);

        return $qb;
    }

    /**
     * Retourne la liste des objets selon le/les types et trié par nombre de vues
     *
     * @param array $types Les types à filtrer
     *
     * @return QueryBuilder
     */
    public function getObjetsByNbVue( $limit = 0 )
    {
      $qb = $this->_em->createQueryBuilder();
      $qb->select('obj')
         ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
         ->leftJoin('obj.types','refTypes')
         ->where('obj.etat = 3')
         ->andWhere('obj.publicationPlusConsulte = :true')
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
         ->setParameters(
            array(
                'today' => new \DateTime(),
                'true'  => true
         ))
         ->orderBy('obj.nbVue', 'DESC');

      if( $limit !== 0 )
        $qb->setMaxResults($limit);

      return $qb;
    }

    /**
     * Retourne l'ensemble des productions actives
     */
    public function getProductionsActive()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->innerJoin('refTypes.parent','refTypesParent')
            ->innerJoin('obj.etat','etat')
            ->where('refTypesParent.id = :idParent')->setParameter('idParent', 175 )
            ->andWhere('etat.id = :idActif')->setParameter('idActif', 3 )
            ->orderBy('obj.alias', 'ASC');
        
        return $qb;
    }

    /**
     * Retourne l'ensemble des productions actives
     */
    public function getObjetsForDashboard()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj.id, obj.nbVue, obj.titre, refType.id as typeId, parentType.id as parentId, refEtat.id as etat, obj.dateCreation, obj.dateDebutPublication, obj.dateFinPublication')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->innerJoin('obj.types','refType')
            ->leftJoin('obj.etat','refEtat')
            ->leftJoin('refType.parent','parentType')
            ->leftJoin('obj.domaines','domaine')
                ->where('domaine.id = :idDomaine')
                ->setParameter('idDomaine', 1)
            ;
        
        return $qb;
    }

    /**
     * Retourne la liste des objets en fonction des dates passées en param
     *
     * @param DateTime $dateDebut Date début fourchette
     * @param DateTime $dateFin   Date fin fourchette
     *
     * @return QueryBuilder
     */
    public function getObjetsByDate( $dateDebut, $dateFin )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.contenus','contenus');

            if(!is_null($dateDebut))
            {
                $qb->andWhere('obj.dateDebutPublication >= :dateDebut')->setParameter('dateDebut', $dateDebut );
            }

            if(!is_null($dateFin))
            {
                $qb->andWhere('obj.dateFinPublication <= :dateFin')->setParameter('dateFin', $dateFin );
            }
            $qb->orderBy('obj.dateDebutPublication', 'DESC');

        return $qb;
    }

  /**
   * Set toute la colone A la une à false
   */
  public function setAllAlaUneFalse($id) {
    $qb = $this->_em->createQueryBuilder();
    $qb->update()
      ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
      ->set('obj.alaune', '0')
      ->where('obj.id != :id')
      ->setParameter('id', $id);
    return $qb;
  }

  /**
   * Retourne l'article à la une
   */
  public function getArticleAlaUne() {
    $qb = $this->_em->createQueryBuilder();
    $qb->select('obj')
      ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
      ->where('obj.alaune = 1');
    return $qb;
  }

    /**
     * Retourne les articles d'une catégorie.
     *
     * @param \HopitalNumerique\ObjetBundle\Manager\Reference $categorie Catégorie
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine  $domaine   Domaine
     * @return array<\HopitalNumerique\ObjetBundle\Entity\Objet> Articles
     */
    public function getArticlesForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getArticlesForCategorieQueryBuilder($categorie, $domaine)->getQuery()->getResult();
    }

    /**
     * Retourne les articles d'une catégorie.
     *
     * @param \HopitalNumerique\ObjetBundle\Manager\Reference $categorie Catégorie
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine  $domaine   Domaine
     * @return array<\HopitalNumerique\ObjetBundle\Entity\Objet> Articles
     */
    public function getArticlesForCategorieQueryBuilder(Reference $categorie, Domaine $domaine)
    {
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query = $this->createQueryBuilder('article');

        $query
            ->andWhere('article.isArticle = :isArticle')
            ->setParameter('isArticle', true)
            ->andWhere($query->expr()->orX('article.dateDebutPublication < :aujourdhui', $query->expr()->isNull('article.dateDebutPublication')))
            ->andWhere($query->expr()->orX('article.dateFinPublication > :aujourdhui', $query->expr()->isNull('article.dateFinPublication')))
            ->setParameter('aujourdhui', $aujourdhui)
            ->innerJoin('article.domaines', 'domaine', Expr\Join::WITH, 'domaine = :domaine')
            ->setParameter('domaine', $domaine)
            ->innerJoin('article.types', 'categorie')
            ->innerJoin('categorie.parent', 'categorieParent')
            ->andWhere($query->expr()->orX('categorie = :categorie', 'categorieParent = :categorie'))
            ->setParameter('categorie', $categorie)
            ->addOrderBy('article.dateCreation', 'DESC')
        ;

        return $query;
    }

    /**
     * Retourne le dernier article d'une catégorie.
     *
     * @param \HopitalNumerique\ObjetBundle\Manager\Reference $categorie Catégorie
     * @return \HopitalNumerique\ObjetBundle\Entity\Objet Dernier article
     */
    public function getLastArticleForCategorie(Reference $categorie, Domaine $domaine)
    {
        return $this->getArticlesForCategorieQueryBuilder($categorie, $domaine)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}