<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\AutodiagBundle\Entity\Model;
use HopitalNumerique\AutodiagBundle\Entity\Model\Preset;
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
     * @param Model $model
     * @return bool
     */
    public function hasPreset(Model $model)
    {
        return $this->getPreset($model) !== null;
    }

    /**
     * Get Preset for Model
     *
     * @param Model $model
     * @return Preset|null
     */
    public function getPreset(Model $model)
    {
        if (null === $model->getId()) {
            return null;
        }

        if (!array_key_exists($model->getId(), $this->presets)) {
            $preset = $this->manager->find(Preset::class, [
                'model' => $model,
                'type' => $this->getName(),
            ]);

            $this->presets[$model->getId()] = $preset;
        }

        return $this->presets[$model->getId()];
    }

    /**
     * Set Preset value for Model
     *
     * @param Model $model
     * @param $value
     */
    public function setPreset(Model $model, $value)
    {
        if (null !== $value || $this->hasPreset($model)) {
            $preset = $this->createPreset($model);
            $preset->setPreset($value);

            $this->presets[$model->getId()] = $preset;

            if (null === $value) {
                $this->manager->remove($preset);
            } else {
                $this->manager->persist($preset);
            }
            $this->manager->flush();
        }
    }

    /**
     * Create Preset object for Model
     *
     * @param Model $model
     * @return Preset
     */
    protected function createPreset(Model $model)
    {
        $preset = $this->getPreset($model) ?: new Preset($model, $this->getName());

        return $preset;
    }
}
