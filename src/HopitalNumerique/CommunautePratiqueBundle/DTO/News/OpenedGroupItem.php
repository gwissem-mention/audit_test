<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

class OpenedGroupItem implements WallItemInterface
{
    /**
     * @var Groupe $group
     */
    protected $group;

    /**
     * @var boolean
     */
    protected $registered;

    /**
     * ClosedGroupItem constructor.
     *
     * @param Groupe $group
     * @param boolean $registered
     */
    public function __construct(Groupe $group, $registered)
    {
        $this->group = $group;
        $this->registered = $registered;
    }

    /**
     * @return Groupe
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->group->getDateInscriptionOuverture();
    }

    /**
     * @return bool
     */
    public function isRegistered()
    {
        return $this->registered;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'opened_group';
    }

}
