<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ItemAttribute
{
    public $label;

    public $responseText;
    public $responseValue;

    public $colored;

    public function __construct($label, $colored = true)
    {
        $this->label = $label;
        $this->colored = $colored;
    }

    public function setResponse($value, $text)
    {
        $this->responseValue = $value;
        $this->responseText = $text;
    }

    public function setRestponseValue($value)
    {
        $this->responseValue = $value;
    }

    public function setResponseText($value)
    {
        $this->responseText = $value;
    }
}
