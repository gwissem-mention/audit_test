<?php
namespace HopitalNumerique\AutodiagBundle\Model;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 * Domain class that represent the admin form for create and update action on a Autodiag
 *
 * @package HopitalNumerique\AutodiagBundle\Domain
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagAdminUpdate
{
    /** @var Autodiag */
    private $autodiag;

    /** @var array */
    private $presets;

    /**
     * @return mixed
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * @param mixed $autodiag
     */
    public function setAutodiag(Autodiag $autodiag)
    {
        $this->autodiag = $autodiag;
    }

    /**
     * @return array
     */
    public function getPresets()
    {
        return $this->presets;
    }

    /**
     * @param Autodiag\Preset $preset
     */
    public function addPreset(Autodiag\Preset $preset)
    {
        $this->presets[] = $preset;
    }
}
