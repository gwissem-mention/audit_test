<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute;

/**
 * AttributeBuilder provider
 * This class contains all available attribute builders, by the CompilerPass
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AttributeBuilderProvider
{
    protected $attributeBuilders;

    public function __construct()
    {
        $this->attributeBuilders = array();
    }

    public function addBuilder(AttributeBuilderInterface $attributeBuilder)
    {
        $this->attributeBuilders[$attributeBuilder->getName()] = $attributeBuilder;
    }

    /**
     * Get builder by type
     *
     * @param $type
     *
     * @return AttributeBuilderInterface|null
     */
    public function getBuilder($type)
    {
        if (array_key_exists($type, $this->attributeBuilders)) {
            return $this->attributeBuilders[$type];
        }

        return null;
    }

    public function getBuildersName()
    {
        return array_values(array_map(function (AttributeBuilderInterface $element) {
            return $element->getName();
        }, $this->attributeBuilders));
    }
}
