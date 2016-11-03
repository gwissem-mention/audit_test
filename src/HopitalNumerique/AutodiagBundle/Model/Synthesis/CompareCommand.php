<?php

namespace HopitalNumerique\AutodiagBundle\Model\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Component\Validator\Constraints as Assert;

class CompareCommand
{
    /**
     * @var Synthesis
     * @Assert\NotNull()
     */
    public $reference;

    /**
     * @var Synthesis
     * @Assert\NotNull()
     */
    public $synthesis;

    /**
     * ComparisonCommand constructor.
     * @param Synthesis $reference
     * @param Synthesis $synthesis
     */
    public function __construct(Synthesis $reference = null, Synthesis $synthesis = null)
    {
        $this->reference = $reference;
        $this->synthesis = $synthesis;
    }
}
