<?php
namespace HopitalNumerique\AutodiagBundle\Model\Result;

class Score
{
    protected $code;

    protected $label;

    protected $value;

    /**
     * Score constructor.
     * @param $code
     * @param $label
     * @param $score
     */
    public function __construct($score, $label = null, $code = null)
    {
        $this->code = $code;
        $this->label = $label;
        $this->value = $score;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
