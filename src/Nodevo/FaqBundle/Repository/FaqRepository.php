<?php

namespace Nodevo\FaqBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FaqRepository
 */
class FaqRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('faq.id, faq.question, faq.reponse, faq.order, categorie.name as categorieName, domaine.nom as domaineNom')
            ->from('NodevoFaqBundle:Faq', 'faq')
            ->leftJoin('faq.categorie', 'categorie')
            ->leftJoin('faq.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            // ->groupBy('faq')
            ->orderBy('faq.order');
            
        return $qb;
    }
}