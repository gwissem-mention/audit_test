<?php

namespace HopitalNumerique\AutodiagBundle\Model;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

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
