<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ItemResponse
{
    protected $value;
    protected $text;
    protected $unit;
    protected $comment;
    protected $score;

    /**
     * @var ItemActionPlan
     */
    protected $actionPlan;

    /**
     * ItemResponse constructor.
     *
     * @param $value
     * @param $text
     * @param $unit
     * @param $comment
     * @param $score
     */
    public function __construct($value, $text, $unit = null, $comment = null, $score = null)
    {
        $this->value = $value;
        $this->text = $text;
        $this->unit = $unit;
        $this->comment = $comment;
        $this->score = $score;
    }

    public function setActionPlan(ItemActionPlan $actionPlan)
    {
        $this->actionPlan = $actionPlan;
    }

    public function getActionPlan()
    {
        return $this->actionPlan;
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return null|string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param null $comment
     *
     * @return ItemResponse
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param null $score
     *
     * @return ItemResponse
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }
}
