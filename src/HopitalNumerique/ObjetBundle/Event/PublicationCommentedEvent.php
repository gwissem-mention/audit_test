<?php

namespace HopitalNumerique\ObjetBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use HopitalNumerique\ObjetBundle\Entity\Commentaire;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class PublicationCommentedEvent.
 */
class PublicationCommentedEvent extends Event
{
    /**
     * @var Commentaire Comment on publication or publication part.
     */
    protected $comment;

    /**
     * PublicationCommentedEvent constructor.
     *
     * @param Commentaire $comment
     */
    public function __construct(Commentaire $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return Commentaire
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return Objet
     */
    public function getObject()
    {
        return $this->comment->getObjet();
    }

    /**
     * @return Contenu|null
     */
    public function getInfradoc()
    {
        return $this->comment->getContenu();
    }
}
