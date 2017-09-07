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
     * @var WidgetExtension[]
     */
    protected $extensions = [];

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

    /**
     * @param WidgetExtension $extension
     *
     * @return Widget
     */
    public function addExtension(WidgetExtension $extension)
    {
        $this->extensions[$extension->getName()] = $extension;

        return $this;
    }

    /**
     * @return WidgetExtension[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Gets extension by name
     *
     * @param $name
     *
     * @return WidgetExtension|null
     * @throws \LogicException
     */
    public function getExtension($name)
    {
        if (!$this->hasExtension($name)) {
            throw new \LogicException('Extension not found');
        }

        return $this->extensions[$name];
    }

    /**
     * Checks if extension exist by name
     *
     * @param $name
     * @return bool
     */
    public function hasExtension($name)
    {
        return array_key_exists($name, $this->extensions);
    }
}
