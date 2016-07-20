<?php
namespace HopitalNumerique\AutodiagBundle\Model\Result;

class Score
{
    protected $reference;

    protected $label;

    protected $value;

    /**
     * Score constructor.
     * @param $reference
     * @param $label
     * @param $score
     */
    public function __construct($score, $label = null, $reference = null)
    {
        $this->reference = $reference;
        $this->label = $label;
        $this->value = $score;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
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
