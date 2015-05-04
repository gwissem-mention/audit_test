<?php

namespace HopitalNumerique\DomaineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class DomaineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'URL du domaine',
                'attr'       => array('class' => 'validate[required,max[255]]')
            ))
            ->add('adresseMailContact', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Adresse mail du contact',
                'attr'       => array('class' => 'validate[required,max[255],custom[email]]')
            ))
            ->add('template', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueDomaineBundle:Template',
                    'property'      => 'nom',
                    'multiple'      => false,
                    'required'      => true,
                    'label'         => 'Template',
                    'empty_value'   => ' - ',
                    'attr'       => array('class' => 'validate[required]')
            ))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\DomaineBundle\Entity\Domaine'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_domaine_domaine';
    }
}
