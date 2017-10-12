<?php

namespace HopitalNumerique\CoreBundle\Domain\Command\Relation;

class LinkObjectCommand
{
    /**
     * @var mixed
     */
    public $sourceObject;

    /**
     * @var mixed
     */
    public $targetObject;

    /**
     * LinkObjectCommand constructor.
     *
     * @param mixed $sourceObject
     * @param mixed $targetObject
     */
    public function __construct($sourceObject, $targetObject)
    {
        $this->sourceObject = $sourceObject;
        $this->targetObject = $targetObject;
    }
}
