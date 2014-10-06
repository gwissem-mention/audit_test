<?php

namespace HopitalNumerique\StatBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité ErrorUrl.
 */
class ErrorUrlManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\StatBundle\Entity\ErrorUrl';

    /**
     * Retourne l'entité trouvée ou en créé une
     *
     * @param string $url URL testée
     *
     * @return array(StatRecherche)
     */
    public function existeErrorByUrl( $url )
    { 
        $errorUrl = is_null( $this->findOneBy(array('url' => $url)) ) ? $this->createEmpty() : $this->findOneBy(array('url' => $url));

        $errorUrl->setDateDernierCheck(new \DateTime());
        if(is_null($errorUrl->getId()) || $errorUrl->getId() == 0)
            $errorUrl->setUrl($url);

        return $errorUrl;
    }
}