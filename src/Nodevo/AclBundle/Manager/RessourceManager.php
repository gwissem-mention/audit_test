<?php

namespace Nodevo\AclBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Ressource
 * 
 * @author Quentin SOMAZZI
 */
class RessourceManager extends BaseManager
{
    protected $_class = '\Nodevo\AclBundle\Entity\Ressource';
    
    /**
     * Retourne la liste des ressources qui matchs l'url passée en paramètre
     *
     * @param string $url Url concernée
     *
     * @return array
     */
    public function getRessourceMatchingUrl( $url )
    {
        $ressources = $this->findAll();
        foreach( $ressources as $ressource ) {
            $pattern = $ressource->getPattern();

            preg_match($pattern, $url, $matches);            

            if (!empty($matches))
                break;
        }
        
        return (!empty($matches)) ? $ressource : null;
    }
}