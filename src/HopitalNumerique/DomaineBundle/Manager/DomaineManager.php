<?php

namespace HopitalNumerique\DomaineBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Domaine.
 */
class DomaineManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\DomaineBundle\Entity\Domaine';

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

}