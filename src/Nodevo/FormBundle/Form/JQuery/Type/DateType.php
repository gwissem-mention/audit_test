<?php

namespace Nodevo\FormBundle\Form\JQuery\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType as BaseDateType;

/**
 * DateType
 *
 * @author Olivier Chauvel <olivier@generation-multiple.com>
 */
class DateType extends BaseDateType
{
	private $options;

	/**
     * Constructs
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
    	parent::__construct($options);
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $configs = $this->options;

        $resolver
            ->setDefaults(array(
                'culture' => 'fr',
                'widget' => 'choice',
                'years'  => range(date('Y') - 5, date('Y') + 5),
                'configs' => array(
                    'dateFormat' => null,
                ),
            ))
            ->setNormalizers(array(
                'configs' => function (Options $options, $value) use ($configs) {
                    $result = array_merge($configs, $value);
                    if ('single_text' !== $options['widget'] || isset($result['buttonImage'])) {
                        $result['showOn'] = 'button';
                    }

                    return $result;
                }
            ));
    }
}
