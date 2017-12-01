<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

class ClosedGroupItem implements WallItemInterface
{
    /**
     * @var Groupe $group
     */
    protected $group;

    /**
     * ClosedGroupItem constructor.
     *
     * @param Groupe $group
     */
    public function __construct(Groupe $group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->group->getTitre();
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->group->getDateFin();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'closed_group';
    }

}
