<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use Nodevo\MailBundle\Manager\MailManager;

/**
 * Manager de Commentaire.
 */
class CommentaireManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire';

    protected $mailManager;

    public function __construct(EntityManager $em, MailManager $mailManager)
    {
        parent::__construct($em);

        $this->mailManager = $mailManager;
    }

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

    /**
     * @param Commentaire $commentaire
     */
    public function save($commentaire)
    {
        parent::save($commentaire);

        $this->mailManager->sendCMCommentaireMail($commentaire);
    }
}
