<?php

namespace HopitalNumerique\AutodiagBundle\Model;

use Doctrine\ORM\EntityManager;

class SynthesisFactory
{
    protected $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function create()
    {
    }
}
