<?php

namespace HopitalNumerique\AutodiagBundle\Model\Autodiag;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Reference;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class SynthesisReference extends Reference
{
    /** @var Synthesis */
    protected $synthesis;

    /**
     * @var string|null
     */
    protected $autodiagEntryIdPath = null;

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

    /**
     * @return null|string
     */
    public function getAutodiagEntryIdPath()
    {
        return $this->autodiagEntryIdPath;
    }

    /**
     * @param null|string $autodiagEntryIdPath
     *
     * @return SynthesisReference
     */
    public function setAutodiagEntryIdPath($autodiagEntryIdPath = null)
    {
        $this->autodiagEntryIdPath = $autodiagEntryIdPath;

        return $this;
    }
}
