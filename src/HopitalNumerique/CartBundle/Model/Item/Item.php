<?php

namespace HopitalNumerique\CartBundle\Model\Item;

use HopitalNumerique\CartBundle\Entity\Item as ItemEntity;
use HopitalNumerique\CartBundle\Model\DomainInterface;

abstract class Item implements \JsonSerializable, DomainInterface
{
    /**
     * @var string $objectTypeName
     */
    protected $objectTypeName;

    /**
     * @var ItemEntity $item
     */
    protected $item;

    abstract public function getObject();

    /**
     * @param ItemEntity $item
     *
     * @return Item
     */
    public function setItem(ItemEntity $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return ItemEntity
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return string[]
     */
    public function getParentsTitle()
    {
        return null;
    }

    /**
     * @return string
     */
    abstract function getTitle();

    /**
     * @return string
     */
    abstract function getObjectType();

    /**
     * @return string
     */
    public function getObjectTypeLabelSlug()
    {
        return $this->getObjectType();
    }

    /**
     * @param $name
     *
     * @return Item
     */
    public function setObjectTypeName($name)
    {
        $this->objectTypeName = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectTypeName()
    {
        return $this->objectTypeName;
    }

    /**
     * @return integer
     */
    abstract function getObjectId();

    /**
     * @return string
     */
    abstract function getRoute();

    /**
     * @return array
     */
    abstract function getRouteParameters();

    /**
     * @return string|null
     */
    public function getUriFragment()
    {
        return null;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'itemId' => $this->getItem()->getId(),
            'objectTypeName' => $this->getObjectTypeName(),
            'objectType' => $this->getObjectType(),
            'objectId' => $this->getObjectId(),
            'route' => $this->getRoute(),
            'routeParameters' => $this->getRouteParameters(),
            'uriFragment' => $this->getUriFragment(),
            'parentsTitle' => $this->getParentsTitle(),
            'title' => $this->getTitle(),
        ];
    }
}
