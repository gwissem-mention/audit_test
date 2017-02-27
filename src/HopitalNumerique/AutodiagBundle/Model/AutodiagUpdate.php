<?php

namespace HopitalNumerique\AutodiagBundle\Model;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Domain class that represent the form for update action on a Autodiag.
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagUpdate
{
    /**
     * @var Autodiag
     * @Assert\Valid
     */
    private $autodiag;

    /**
     * @var array<Autodiag\Preset>
     * @Assert\Valid
     */
    private $presets;

    public function __construct(Autodiag $autodiag, $presets)
    {
        $this->autodiag = $autodiag;
        $this->presets = $presets;
    }

    /**
     * @return mixed
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * @return array
     */
    public function getPresets()
    {
        return $this->presets;
    }
}
