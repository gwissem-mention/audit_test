<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class Item
{
    const ITEM_PRIORITY_QUESTIONNAIRE = 'questionnaire';
    const ITEM_PRIORITY_PRIORISE = 'priorisé';

    protected $label;

    /**
     * @var Score
     */
    protected $score;

    /**
     * @var Score[]
     */
    protected $references = [];

    /**
     * @var Item[]
     */
    protected $childrens = [];

    protected $numberOfQuestions;

    protected $numberOfAnswers;

    /**
     * @var ItemAttribute[]
     */
    protected $attributes = [];

    protected $colorationInversed = false;

    /**
     * @var ItemActionPlan
     */
    protected $actionPlan;

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return Score
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param Score $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return Score[]
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param Score[] $references
     */
    public function setReferences($references)
    {
        $this->references = $references;
    }

    public function addReference(Score $score)
    {
        $this->references[$score->getCode()] = $score;
    }

    /**
     * @return Item[]
     */
    public function getChildrens()
    {
        return $this->childrens;
    }

    /**
     * @param Item[] $childrens
     */
    public function setChildrens($childrens)
    {
        $this->childrens = $childrens;
    }

    public function addChildren(Item $children)
    {
        $this->childrens[] = $children;
    }

    /**
     * @return mixed
     */
    public function getNumberOfQuestions()
    {
        return $this->numberOfQuestions;
    }

    /**
     * @param mixed $numberOfQuestions
     */
    public function setNumberOfQuestions($numberOfQuestions)
    {
        $this->numberOfQuestions = $numberOfQuestions;
    }

    /**
     * @return mixed
     */
    public function getNumberOfAnswers()
    {
        return $this->numberOfAnswers;
    }

    /**
     * @param mixed $numberOfAnswers
     */
    public function setNumberOfAnswers($numberOfAnswers)
    {
        $this->numberOfAnswers = $numberOfAnswers;
    }

    public function addAttribute(ItemAttribute $attribute)
    {
        $this->attributes[] = $attribute;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setActionPlan(ItemActionPlan $actionPlan)
    {
        $this->actionPlan = $actionPlan;
    }

    public function getActionPlan()
    {
        return $this->actionPlan;
    }

    public function isColorationInversed()
    {
        return $this->colorationInversed;
    }

    public function setColorationInversed($inversed)
    {
        $this->colorationInversed = $inversed;
    }
}
