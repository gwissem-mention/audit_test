<?php
namespace HopitalNumerique\AutodiagBundle\Form\Type;

use HopitalNumerique\AutodiagBundle\Form\DataTransformer\Model\Preset\OptionToCsvTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CsvType is a textarea field that transform CSV liek syntax to associative array
 *
 * @package HopitalNumerique\AutodiagBundle\Form\Type
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class CsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new OptionToCsvTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'ad.autodiag.csv.incorect_format',
        ));
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}
