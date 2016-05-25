<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

/**
 * Radio attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class RadioBuilder extends AbstractBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'radio';
    }
}
