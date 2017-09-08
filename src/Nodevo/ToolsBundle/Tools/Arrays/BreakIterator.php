<?php

namespace Nodevo\ToolsBundle\Tools\Arrays;

/**
 * Class BreakIterator.
 * This class is used to help dealing with breaks in arrays.
 *
 * @package Nodevo\ToolsBundle\Tools\Arrays
 */
class BreakIterator implements \Iterator
{
    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var \Closure[] $changeTrackers
     */
    protected $changeTrackers;

    /**
     * @var array $changeValues
     */
    protected $changeValues;

    /**
     * @var array $previousValues
     */
    protected $previousValues;

    /**
     * @var array $changed
     */
    protected $changed;

    /**
     * @var integer $counter
     */
    protected $counter;

    /**
     * BreakIterator constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Set iterator data.
     *
     * @param array $data
     *
     * @return BreakIterator
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->init();

        return $this;
    }

    /**
     * Set change trackers.
     *
     * @param \Closure[] $trackers
     *
     * @return BreakIterator
     */
    public function setChangeTrackers(array $trackers = [])
    {
        $this->changeTrackers = [];

        return $this->addChangeTrackers($trackers);
    }

    /**
     * Add change trackers.
     *
     * @param \Closure[] $trackers
     *
     * @return BreakIterator
     */
    public function addChangeTrackers(array $trackers = [])
    {
        foreach ($trackers as $trackerName => $tracker) {
            $this->addChangeTracker($trackerName, $tracker);
        }

        return $this;
    }

    /**
     * Add a change tracker.
     *
     * @param $trackerName
     * @param \Closure $tracker
     *
     * @return BreakIterator
     */
    public function addChangeTracker($trackerName, \Closure $tracker)
    {
        $this->changeTrackers[$trackerName] = $tracker;

        return $this;
    }

    /**
     * Rewind array.
     */
    public function rewind()
    {
        reset($this->data);
        $this->init();
    }

    /**
     * Return current item
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Return current key
     *
     * @return int|null|string
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Go to  next item.
     *
     * @return mixed
     */
    public function next()
    {
        $next = next($this->data);
        if ($next !== false) {
            $this->counter++;
            $this->updateTrackingValues();
        }
        return $next;
    }

    /**
     * Return whether array pointer is valid or not.
     *
     * @return bool
     */
    public function valid()
    {
        return null !== key($this->data);
    }

    /**
     * Check if a tracker value has changed.
     *
     * @param $trackerName
     *
     * @return bool
     */
    public function hasChanged($trackerName)
    {
        return array_key_exists($trackerName, $this->changed) && true === $this->changed[$trackerName];
    }

    /**
     * Return previous value.
     *
     * @param $trackerName
     *
     * @return mixed
     */
    public function getPreviousValue($trackerName)
    {
        return $this->previousValues[$trackerName];
    }

    /**
     * Check if pointer is at last item.
     *
     * @return bool
     */
    public function isLast()
    {
        return count($this->data) === $this->counter;
    }

    /**
     * Init.
     */
    protected function init()
    {
        $this->changeValues = $this->changed = [];
        $this->counter = 1;
    }

    /**
     * Update values for each tracker and check for changes.
     */
    protected function updateTrackingValues()
    {
        $this->previousValues = $this->changeValues;
        $hasChanged = false;
        $value = $this->current();
        foreach ($this->changeTrackers as $trackerName => $tracker) {
            $newValue = $tracker($value);
            if (false === $hasChanged && array_key_exists($trackerName, $this->changeValues)) {
                if ($this->changeValues[$trackerName] !== $newValue) {
                    $hasChanged = true;
                }
            }
            $this->changed[$trackerName] = $hasChanged;
            $this->changeValues[$trackerName] = $newValue;
        }
    }
}
