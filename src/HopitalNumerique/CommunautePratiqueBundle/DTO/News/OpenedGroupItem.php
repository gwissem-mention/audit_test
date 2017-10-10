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
     * ClosedGroupItem constructor.
     *
     * @param Groupe $group
     */
    public function __construct(Groupe $group)
    {
        $this->group = $group;
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
     * @return string
     */
    public function getType()
    {
        return 'opened_group';
    }

}
