<?php
namespace HopitalNumerique\ReferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

class ReferenceLockedType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'required' => true,
                'label'    => 'Libellé du concept',
                'attr'     => [
                    'maxlength'            => 255,
                    'class'                => 'validate[required]',
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_reference_reference_locked';
    }
}
