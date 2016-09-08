<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ItemActionPlan
{
    private $value;
    private $description;
    private $link;
    private $linkDescription;
    private $visible;

    public function __construct($value, $description, $link, $linkDescription, $visible)
    {
        $this->value = $value;
        $this->description = $description;
        $this->link = $link;
        $this->linkDescription = $linkDescription;
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
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return mixed
     */
    public function getLinkDescription()
    {
        return $this->linkDescription;
    }

    public function isVisible()
    {
        return $this->visible;
    }
}
