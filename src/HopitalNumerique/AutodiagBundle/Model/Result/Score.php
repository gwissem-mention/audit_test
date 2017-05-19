<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class Score
{
    protected $code;

    protected $label;

    protected $value;

    protected $color = null;

    /**
     * Score constructor.
     *
     * @param      $score
     * @param null $label
     * @param null $code
     * @param null $color
     */
    public function __construct($score, $label = null, $code = null, $color = null)
    {
        $this->code = $code;
        $this->label = $label;
        $this->value = null !== $score ? round($score, 0) : null;
        $this->color = $color;
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
        return $this->label ?: 'Score';
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param null $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }
}
