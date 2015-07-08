<?php

namespace Nodevo\AclBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Cache\ApcCache;

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
        $cacheDriver = new ApcCache();

        if ($cacheDriver->contains("_acl_ressources_all"))
        {
            $ressources = $cacheDriver->fetch("_acl_ressources_all");
        }
        else
        {
            $ressources = $this->findAll();

            $cacheDriver->save("_acl_ressources_all", $ressources, "86400");
        }

        foreach( $ressources as $ressource ) {
            $pattern = $ressource->getPattern();

            preg_match($pattern, $url, $matches);            

            if (!empty($matches))
                break;
        }
        
        return (!empty($matches)) ? $ressource : null;
    }
}