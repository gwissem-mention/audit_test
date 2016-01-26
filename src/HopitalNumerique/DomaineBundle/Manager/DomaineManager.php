<?php

namespace HopitalNumerique\DomaineBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Domaine.
 */
class DomaineManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\DomaineBundle\Entity\Domaine';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $domainesForGrid = array();
        $domaines = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();

        foreach ($domaines as $domaine) {
            $domaine['idDomaine'] = $domaine['id'];
            $domainesForGrid[] = $domaine;
        }
        return $domainesForGrid;
    }
    
    public function getDomainesUserConnected($idUser) {
    	return $this->getRepository()->getDomainesUserConnectedForForm($idUser)->getQuery()->getResult();
    }

    public function getDomaineFromHttpHost($httpHost)
    {
        return $this->getRepository()->getDomaineFromHttpHost($httpHost)->getQuery()->getOneOrNullResult();
    }

    public function getDomainesByUsers()
    {
        $domaines = $this->findAll();
        $domaineByUser = array();

        foreach ($domaines as $domaine) 
        {
            foreach ($domaine->getUsers() as $user) 
            {
                if(!array_key_exists($user->getId(), $domaineByUser))
                {
                    $domaineByUser[$user->getId()] = array(
                        'url' => '',
                        'id'  => array()
                    );
                }

                $domaineByUser[$user->getId()]['url'] .= ($domaineByUser[$user->getId()]['url'] != '' ?  ' - ' : '') . $domaine->getNom();
                $domaineByUser[$user->getId()]['id'][] = $domaine->getId();
            }
        }

        return $domaineByUser;
    }

    /**
     * Récupère l'ensemble des domaines trié par id
     *
     * @return [type]
     */
    public function getAllDomainesOrdered()
    {
        $domaineOrdered = array();

        $domaines = $this->findAll();

        foreach ($domaines as $domaine) 
        {
            $domaineOrdered[$domaine->getId()] = $domaine;
        }

        return $domaineOrdered;
    }

    /**
     * Récupération des domaines correspondant au forum passé en param
     *
     * @param [type] $idForum [description]
     *
     * @return [type]
     */
    public function getDomaineForForumId($idForum)
    {
        return $this->getRepository()->getDomaineForForumId($idForum)->getQuery()->getResult();
    }
}