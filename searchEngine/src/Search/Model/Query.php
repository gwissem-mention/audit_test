<?php

namespace Search\Model;

use Search\Model\Query\Filter;

/**
 * Query object
 */
class Query
{
    /**
     * @var string
     */
    protected $index;

    /**
     * @var string
     */
    protected $term;

    /**
     * @var int
     */
    protected $size = 10;

    /**
     * @var int
     */
    protected $from = 0;

    /**
     * @var Filter[]
     */
    protected $filters;

    /**
     * @var string
     */
    protected $source;

    /**
     * Query constructor.
     *
     * @param $index
     */
    public function __construct($index)
    {
        $this->index = $index;
        $this->filters = [];
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param mixed $term
     *
     * @return Query
     */
    public function setTerm($term)
    {
        $this->term = $term;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return Query
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int $from
     *
     * @return Query
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param Filter $filter
     *
     * @return $this
     */
    public function addFilter(Filter $filter)
    {
        foreach ($this->getFilters() as $existingFilter) {
            if ($existingFilter == $filter) {
                return $this;
            }
        }

        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }
}
