<?php

namespace HopitalNumerique\FlashBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Flash.
 */
class FlashManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\FlashBundle\Entity\Flash';

    /**
     * Récupère les messages visibles par l'utilisateur
     *
     * @return array
     */
    public function getMessagesForUser( $user )
    {
        return $this->getRepository()->getMessagesForUser( $user )->getQuery()->getResult();
    }
}