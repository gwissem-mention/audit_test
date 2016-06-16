<?php
namespace HopitalNumerique\AutodiagBundle\Model;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 * Domain class that represent the form for update action on a Autodiag
 *
 * @package HopitalNumerique\AutodiagBundle\Domain
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagUpdate
{
    /** @var Autodiag */
    private $autodiag;

    /** @var array<Autodiag\Preset> */
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
