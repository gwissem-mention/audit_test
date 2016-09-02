<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ItemActionPlan
{
    private $value;
    private $description;
    private $link;
    private $linkDescription;

    public function __construct($value, $description, $link, $linkDescription)
    {
        $this->value = $value;
        $this->description = $description;
        $this->link = $link;
        $this->linkDescription = $linkDescription;
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
}
