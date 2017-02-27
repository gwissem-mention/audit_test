<?php

namespace Search\Model\Query;

/**
 * Query filter
 */
class Filter
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $negative = false;

    /**
     * Filter constructor.
     *
     * @param $field
     * @param $value
     * @param bool $negative
     */
    public function __construct($field, $value, $negative = false)
    {
        $this->field = $field;
        $this->value = $value;
        $this->negative = $negative;
    }


    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isNegative()
    {
        return $this->negative;
    }
}
