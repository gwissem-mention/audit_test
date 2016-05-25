<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

/**
 * Text attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class TextBuilder extends AbstractBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'texte';
    }
}
