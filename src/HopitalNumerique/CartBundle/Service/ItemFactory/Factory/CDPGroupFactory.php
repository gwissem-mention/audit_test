<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Model\Item\CDPGroup;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository;

class CDPGroupFactory extends Factory
{
    /**
     * @var GroupeRepository $groupRepository
     */
    protected $groupRepository;

    /**
     * CDPGroupFactory constructor.
     *
     * @param GroupeRepository $containerRepository
     */
    public function __construct(GroupeRepository $containerRepository)
    {
        $this->groupRepository = $containerRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::CDP_GROUP_TYPE;
    }

    /**
     * @param $content
     *
     * @return CDPGroup
     */
    public function build($content)
    {
        return new CDPGroup($content);
    }

    /**
     * @param $itemIds
     *
     * @return Groupe[]
     */
    public function getMultiple($itemIds)
    {
        return $this->groupRepository->findById($itemIds);
    }

    /**
     * @param $itemId
     *
     * @return null|Groupe
     */
    public function get($itemId)
    {
        return $this->groupRepository->find($itemId);
    }
}
