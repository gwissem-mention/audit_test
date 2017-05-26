<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ItemActionPlan
{
    private $value;
    private $description;
    private $links;
    private $visible;

    public function __construct($value, $description, $links, $visible)
    {
        $this->value = $value;
        $this->description = $description;
        $this->links = $links;
        $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getLinks()
    {
        return $this->links;
    }

    public function isVisible()
    {
        return $this->visible;
    }
}
