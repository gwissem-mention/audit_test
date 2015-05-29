<?php

namespace HopitalNumerique\DomaineBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * DomaineRepository
 */
class DomaineRepository extends EntityRepository
{
    public function getDomainesUserConnectedForForm($idUser)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('domaine')
            ->from('HopitalNumeriqueDomaineBundle:Domaine', 'domaine')
            ->leftJoin('domaine.users','user')
            ->where('user.id = :idUser')
            ->setParameter('idUser', $idUser);
            
        return $qb;
    }

    /**
     * Retourne le domaine correspondant à l'host passé en parametre
     *
     * @param string $httpHost [description]
     *
     * @return QueryBuilder
     */
    public function getDomaineFromHttpHost($httpHost)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('domaine')
            ->from('HopitalNumeriqueDomaineBundle:Domaine', 'domaine')
            ->leftJoin('domaine.template', 'template')
            ->where('domaine.url LIKE :httpHost')
            ->setParameter('httpHost', ('%' . $httpHost . '%') );
            
        return $qb;
    }

    /**
     * Retourne le(s) domaine(s) du forum passé en param
     *
     * @param int $idForum [description]
     *
     * @return QueryBuilder
     */
    public function getDomaineForForumId($idForum)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('domaine')
            ->from('HopitalNumeriqueDomaineBundle:Domaine', 'domaine')
            ->leftJoin('domaine.forums', 'forum')
                ->where('forum.id = :idForum')
                ->setParameter('idForum', $idForum );
            
        return $qb;
    }
}