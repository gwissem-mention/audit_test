<?php

namespace HopitalNumerique\ExpertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class EvenementExpertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Type d\'activité',
                'empty_value'   => ' - ',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'TYPE_EVENEMENT')
                              ->orderBy('ref.libelle', 'ASC');
                },
                'attr'         => array('class' => 'validate[required]')
            ))
            ->add('nbVacation', 'integer', array(
                'required'   => true, 
                'label'      => 'Nombre de vacations',
                'attr'        => array(
                        'class' => 'validate[required,custom[integer],min[0]]'
                )
            ))
            ->add('date', 'genemu_jquerydate', array(
                'required' => true, 
                'label'    => 'Date',
                'widget'   => 'single_text',
                'attr'     => array('class' => 'validate[required] datepicker' )
            ))       
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ExpertBundle\Entity\EvenementExpert'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_expert_evenementexpert';
    }
}
