<?php

namespace HopitalNumerique\ForumBundle\Component\BBCode;

class TagIntegrator
{
    protected $tags = [];

    public function __construct($tags)
    {
        $this->tags = $tags;
    }

    public function build()
    {
        return $this->tags;
    }
}
