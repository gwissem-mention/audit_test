<?php

namespace HopitalNumerique\NewAccountBundle\Model\Widget;

class Widget
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var boolean $sticked
     */
    protected $sticked = false;

    /**
     * Widget constructor.
     *
     * @param string $name
     * @param string $title
     * @param string $content
     * @param boolean $sticked
     */
    public function __construct($name, $title, $content, $sticked = false)
    {
        $this->name = $name;
        $this->title = $title;
        $this->content = $content;
        $this->sticked = $sticked;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isSticked()
    {
        return $this->sticked;
    }
}
