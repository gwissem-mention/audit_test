<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Preset attribute options.
 *
 * @ORM\Table(name="ad_autodiag_preset")
 * @ORM\Entity
 */
class Preset
{
    /**
     * Model.
     *
     * @var Autodiag
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag", inversedBy="presets")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $autodiag;

    /**
     * Attribute type.
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * Preset value.
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $preset;

    /**
     * Preset constructor.
     *
     * @param Autodiag $autodiag
     * @param $type
     */
    public function __construct(Autodiag $autodiag, $type)
    {
        $this->autodiag = $autodiag;
        $this->type = $type;

        $this->preset = [];
    }

    /**
     * Get model.
     *
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * Get attribute type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get preset value.
     *
     * @return array
     */
    public function getPreset()
    {
        return $this->preset;
    }

    /**
     * Set preset value.
     *
     * @param array $preset
     *
     * @return $this
     */
    public function setPreset($preset)
    {
        $this->preset = $preset;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validatePresets(ExecutionContextInterface $context)
    {
        $values = $this->getPreset();
        if (null !== $values) {
            $empties = 0;
            foreach ($values as $value) {
                if (count($value) === 0) {
                    ++$empties;
                }
            }
            if ($empties > 0 && $empties < count($values)) {
                $context->buildViolation('Toutes les valeurs doivent êtres renseignées')
                    ->addViolation();
            }
        }
    }
}
