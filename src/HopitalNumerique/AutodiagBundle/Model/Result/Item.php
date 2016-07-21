<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class Item
{
    protected $label;

    /**
     * @var Score
     */
    protected $score;

    /**
     * @var Score[]
     */
    protected $references;

    /**
     * @var Item[]
     */
    protected $childrens;

    protected $numberOfQuestions;

    protected $numberOfAnswers;

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
}
