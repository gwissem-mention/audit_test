<?php

namespace HopitalNumerique\PaiementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateCreation', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'dateCreation'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\PaiementBundle\Entity\Facture'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_paiement_facture';
    }
}
