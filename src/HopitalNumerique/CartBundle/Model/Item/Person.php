<?php

namespace HopitalNumerique\CartBundle\Model\Item;

use HopitalNumerique\UserBundle\Entity\User;

class Person extends Item
{
    /**
     * @var User $person
     */
    protected $person;

    /**
     * Person constructor.
     *
     * @param User $person
     */
    public function __construct(User $person)
    {
        $this->person = $person;
    }

    /**
     * @return User
     */
    public function getObject()
    {
        return $this->person;
    }

    public function getTitle()
    {
        return $this->person->getNomPrenom();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return \HopitalNumerique\CartBundle\Entity\Item::PERSON_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->person->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [
            'id' => $this->person->getId(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return $this->person->getDomaines();
    }
}
