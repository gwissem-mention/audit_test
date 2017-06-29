<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Model\Item\Objet;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;

class ObjectFactory extends Factory
{
    /**
     * @var ObjetRepository $objectRepository
     */
    protected $objectRepository;

    /**
     * ObjectFactory constructor.
     *
     * @param ObjetRepository $objectRepository
     */
    public function __construct(ObjetRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::OBJECT_TYPE;
    }

    /**
     * @param $object
     *
     * @return Objet
     */
    public function build($object)
    {
        return new Objet($object);
    }

    /**
     * @param array $itemIds
     *
     * @return array
     */
    public function getMultiple($itemIds)
    {
        return $this->objectRepository->findByIdsWithJoin($itemIds);
    }

    /**
     * @param $itemId
     *
     * @return null|object
     */
    public function get($itemId)
    {
        return $this->objectRepository->findByIdWithJoin($itemId);
    }
}
