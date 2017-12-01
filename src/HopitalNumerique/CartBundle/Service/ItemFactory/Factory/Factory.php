<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Model\Item\Item;

abstract class Factory
{
    /**
     * @var Item[] $items
     */
    protected $items;

    /**
     * @param integer $itemId
     *
     * @return Item
     */
    public function getItem($itemId)
    {
        if (isset($this->items[$itemId])) {
            return $this->items[$itemId];
        }

        if (null === ($obj = $this->get($itemId))) {
            return null;
        }

        return $this->items[$itemId] = $this->build($obj);
    }

    /**
     * @param array $itemIds
     */
    public function prepare($itemIds)
    {
        foreach ($this->getMultiple($itemIds) as $object) {
            $this->items[$object->getId()] = $this->build($object);
        }
    }

    /**
     * @param $object
     *
     * @return Item
     */
    abstract public function build($object);

    /**
     * @param integer $itemId
     *
     * @return mixed
     */
    abstract public function get($itemId);

    /**
     * @param array $itemIds
     *
     * @return array
     */
    abstract public function getMultiple($itemIds);

    /**
     * @return string
     */
    abstract public function getType();
}
