<?php
namespace HopitalNumerique\ForumBundle\Component\BBCode;


class TagIntegrator
{
    protected $tags = array();

    public function __construct($tags)
    {
        $this->tags = $tags;
    }

    /**
     *
     * @access public
     */
    public function build()
    {
        return $this->tags;
    }
}