<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value;
use HopitalNumerique\AutodiagBundle\Service\Attribute\PresetableAttributeBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Common builder for all Presetable Builders
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
abstract class AbstractPresetableBuilder extends AbstractBuilder implements PresetableAttributeBuilderInterface
{
    protected $presets = [];

    /** @var ObjectManager */
    protected $manager;

    /** @var FormFactoryInterface  */
    protected $formFactory;

    /**
     * AbstractPresetableBuilder constructor.
     * @param ObjectManager $manager
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(ObjectManager $manager, FormFactoryInterface $formFactory)
    {
        $this->manager = $manager;
        $this->formFactory = $formFactory;
    }

    /**
     * Check if attribute type has builder for Model
     *
     * @param Autodiag $autodiag
     * @return bool
     */
    public function hasPreset(Autodiag $autodiag)
    {
        return $this->getPreset($autodiag) !== null;
    }

    /**
     * Get Preset for Autodiag
     *
     * @param Autodiag $autodiag
     * @return Preset|null
     */
    public function getPreset(Autodiag $autodiag)
    {
        if (null === $autodiag->getId()) {
            return null;
        }

        if (!array_key_exists($autodiag->getId(), $this->presets)) {
            $preset = $this->manager->find(Preset::class, [
                'autodiag' => $autodiag,
                'type' => $this->getName(),
            ]);

            $this->presets[$autodiag->getId()] = $preset;
        }

        return $this->presets[$autodiag->getId()];
    }

    /**
     * Set Preset value for Model
     *
     * @param Autodiag $autodiag
     * @param $value
     */
    public function setPreset(Autodiag $autodiag, $value)
    {
        if (null !== $value || $this->hasPreset($autodiag)) {
            $preset = $this->createPreset($autodiag);
            $preset->setPreset($value);

            $this->presets[$autodiag->getId()] = $preset;

            if (null === $value) {
                $this->manager->remove($preset);
            } else {
                $this->manager->persist($preset);
            }
            $this->manager->flush();
        }
    }

    public function getPresetMinScore(Autodiag $autodiag)
    {
        $presets = $this->getPreset($autodiag)->getPreset();
        $presets = array_map(function ($element) {
            return min(array_keys($element));
        }, $presets);
        return array_sum($presets) / count($presets);
    }

    public function getPresetMaxScore(Autodiag $autodiag)
    {
        $presets = $this->getPreset($autodiag)->getPreset();
        $presets = array_map(function ($element) {
            return max(array_keys($element));
        }, $presets);
        return array_sum($presets) / count($presets);
    }

    public function getFormHtml(Value $entryValue, $viewData = [])
    {
        return parent::getFormHtml($entryValue, array_merge($viewData, [
            'preset' => $this->getPreset($entryValue->getAttribute()->getAutodiag())
        ]));
    }

    /**
     * Create Preset object for Model
     *
     * @param Autodiag $autodiag
     * @return Preset
     */
    protected function createPreset(Autodiag $autodiag)
    {
        $preset = $this->getPreset($autodiag) ?: new Preset($autodiag, $this->getName());

        return $preset;
    }
}
