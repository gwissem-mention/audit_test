<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ItemAttribute
{
    public $label;

    /** @var ItemResponse */
    public $response;

    public $colored;
    public $colorationInversed = false;

    public $weight;

    public $attributeId;

    public function __construct($label, $colored = true)
    {
        $this->label = $label;
        $this->colored = $colored;
    }

    public function setResponse($responseValue, $responseText, $responseUnit = null, $comment = null, $score = null)
    {
        $response = new ItemResponse($responseValue, $responseText, $responseUnit, $comment, $score);
        $this->response = $response;
    }

    public function isColorationInversed()
    {
        return $this->colorationInversed;
    }

    public function setColorationInversed($inversed)
    {
        $this->colorationInversed = $inversed;
    }

    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function setActionPlan(ItemActionPlan $actionPlan)
    {
        if (null !== $this->response) {
            $this->response->setActionPlan($actionPlan);
        }
    }

    public function getActionPlan()
    {
        if (null === $this->response) {
            return null;
        }

        return $this->response->getActionPlan();
    }
}
