<?php

namespace HopitalNumerique\ObjetBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class PublicationNotifiedEvent.
 */
class PublicationNotifiedEvent extends Event
{
    /**
     * @var Objet Publication
     */
    protected $objet;

    /**
     * @var Contenu Publication part
     */
    protected $infradoc;

    /**
     * @var string $reason Reason of update
     */
    protected $reason;

    /**
     * PublicationNotifiedEvent constructor.
     *
     * @param Objet        $objet    Publication
     * @param Contenu|null $infradoc Publication part
     * @param string       $reason   Update reason
     */
    public function __construct(Objet $objet, Contenu $infradoc = null, $reason = '')
    {
        $this->objet = $objet;
        $this->infradoc = $infradoc;
        $this->reason = $reason;
    }

    /**
     * @return Objet
     */
    public function getObject()
    {
        return $this->objet;
    }

    /**
     * @return Contenu
     */
    public function getInfradoc()
    {
        return $this->infradoc;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
