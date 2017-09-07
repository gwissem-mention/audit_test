<?php

namespace HopitalNumerique\CartBundle\Model\Item;


use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

class CDPGroup extends Item
{
    /**
     * @var Groupe $group
     */
    protected $group;

    /**
     * Publication constructor.
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
    public function getObject()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->group->getTitre();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return \HopitalNumerique\CartBundle\Entity\Item::CDP_GROUP_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->group->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'hopitalnumerique_communautepratique_groupe_view';
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [
            'groupe' => $this->group->getId(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return [$this->group->getDomaine()];
    }
}
