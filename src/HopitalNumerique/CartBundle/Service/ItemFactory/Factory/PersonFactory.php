<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Model\Item\Person;
use HopitalNumerique\UserBundle\Repository\UserRepository;

class PersonFactory extends Factory
{
    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * PersonFactory constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::PERSON_TYPE;
    }

    /**
     * @param $object
     *
     * @return Person
     */
    public function build($object)
    {
        return new Person($object);
    }

    /**
     * @param array $itemIds
     *
     * @return User[]
     */
    public function getMultiple($itemIds)
    {
        return $this->userRepository->findById($itemIds);
    }


    /**
     * @param $itemId
     *
     * @return null|User
     */
    public function get($itemId)
    {
        return $this->userRepository->find($itemId);
    }
}
