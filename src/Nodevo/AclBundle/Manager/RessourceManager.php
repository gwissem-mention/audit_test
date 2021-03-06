<?php

namespace Nodevo\AclBundle\Manager;

use Nodevo\AclBundle\Entity\Ressource;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Ressource.
 *
 * @author Quentin SOMAZZI
 */
class RessourceManager extends BaseManager
{
    protected $class = '\Nodevo\AclBundle\Entity\Ressource';

    protected $resourceList;

    /**
     * Retourne la liste des ressources qui matchs l'url passée en paramètre.
     *
     * @param string $url Url concernée
     *
     * @return array
     */
    public function getRessourceMatchingUrl($url)
    {
        $ressources = $this->findAll();
        usort($ressources, function (Ressource $a, Ressource $b) {
            return $a->getOrder() > $b->getOrder();
        });

        foreach ($ressources as $ressource) {
            $pattern = $ressource->getPattern();

            preg_match($pattern, $url, $matches);

            if (!empty($matches)) {
                break;
            }
        }

        return (!empty($matches)) ? $ressource : null;
    }

    public function findAll()
    {
        if (null === $this->resourceList) {
            $this->resourceList = parent::findAll();
        }

        return $this->resourceList;
    }
}
