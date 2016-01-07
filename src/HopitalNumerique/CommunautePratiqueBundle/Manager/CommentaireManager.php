<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

/**
 * Manager de Commentaire.
 */
class CommentaireManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $_class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire';

    /**
     * Retourne true si le document n'est appelé dans aucun commentaire
     * @return boolean
     */
    public function safeDelete($document) {
        $result =  $this->getRepository()->safeDelete($document)->getQuery()->getResult();

        if(!empty($result)) {
            return false;
        }

        return true;
    }
}
