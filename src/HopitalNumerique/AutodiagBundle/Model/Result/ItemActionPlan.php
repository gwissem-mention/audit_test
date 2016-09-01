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
}
