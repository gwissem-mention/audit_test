<?php

namespace HopitalNumerique\AutodiagBundle\Model\Autodiag;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Reference;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class SynthesisReference extends Reference
{
    /** @var Synthesis */
    protected $synthesis;

    public static function create(Synthesis $synthesis, $number)
    {
        $self = new self($number, $synthesis->getAutodiag());
        $self->synthesis = $synthesis;

        return $self;
    }

    /**
     * @return Synthesis
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }
}
