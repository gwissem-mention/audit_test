<?php

namespace HopitalNumerique\NewAccountBundle\Model\Widget;

class WidgetExtension
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $content
     */
    protected $content;

    /**
     * @var integer $position
     */
    protected $position = 0;

    /**
     * WidgetExtension constructor.
     *
     * @param string $name
     * @param string $content
     * @param int $position
     */
    public function __construct($name, $content, $position = 0)
    {
        $this->name = $name;
        $this->content = $content;
        $this->position = $position;
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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
