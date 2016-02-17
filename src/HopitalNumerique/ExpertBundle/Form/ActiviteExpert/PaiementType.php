<?php
namespace HopitalNumerique\ExpertBundle\Form\ActiviteExpert;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form PaiementType.
 */
class PaiementType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vacationsCount', 'number', array(
                'attr' => array(
                    'class' => 'validate[required,custom[number]]'
                )
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ExpertBundle\Entity\ActiviteExpert\Paiement'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_expert_activiteexpert_paiement';
    }
}
