<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

/**
 * Select attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class SelectBuilder extends AbstractBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'liste';
    }
}
