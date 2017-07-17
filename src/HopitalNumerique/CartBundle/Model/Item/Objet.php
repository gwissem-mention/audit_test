<?php

namespace HopitalNumerique\CartBundle\Model\Item;

use HopitalNumerique\ObjetBundle\Entity\Objet as ObjectEntity;

class Objet extends Item
{
    /**
     * @var ObjectEntity $object
     */
    protected $object;

    /**
     * Publication constructor.
     *
     * @param ObjectEntity $object
     */
    public function __construct(ObjectEntity $object)
    {
        $this->object = $object;
    }

    /**
     * @return ObjectEntity
     */
    public function getObject()
    {
        return $this->object;
    }

    public function getParentTitle()
    {
        return null;
    }

    public function getTitle()
    {
        return $this->object->getTitre();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        if ($this->object->isPointDur()) {
            return \HopitalNumerique\CartBundle\Entity\Item::HOT_POINT_TYPE;
        }

        return \HopitalNumerique\CartBundle\Entity\Item::OBJECT_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->object->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'hopital_numerique_publication_publication_objet';
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [
            'id' => $this->object->getId(),
            'alias' => $this->object->getAlias(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return $this->object->getDomaines();
    }
}
