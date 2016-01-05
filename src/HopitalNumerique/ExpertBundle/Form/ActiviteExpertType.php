<?php

namespace HopitalNumerique\ExpertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ActiviteExpertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Titre',
                'attr'       => array('class' => 'validate[required]')
            ))
            ->add('typeActivite', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Type d\'activité',
                'empty_value'   => ' - ',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'ACTIVITE_TYPE')
                              ->orderBy('ref.libelle', 'ASC');
                },
                'attr'         => array('class' => 'validate[required]')
            ))
            ->add('dateDebut', 'genemu_jquerydate', array(
                'required' => true, 
                'label'    => 'Date de début',
                'widget'   => 'single_text',
                'attr'     => array('class' => 'validate[required] datepicker' )
            ))
            ->add('dateFin', 'genemu_jquerydate', array(
                'required' => true, 
                'label'    => 'Date de fin',
                'widget'   => 'single_text',
                'attr'     => array('class' => 'validate[required] datepicker' )
            ))
            ->add('expertConcernes', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueUserBundle:User',
                    'property'      => 'appellation',
                    'multiple'      => true,
                    'required'      => true,
                    'label'         => 'Expert(s) concerné(s)',
                    'empty_value'   => ' - ',
                    'attr'       => array('class' => 'select2'),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->findUsersByRole('ROLE_EXPERT_6');
                    },
            ))
            ->add('nbVacationParExpert', 'integer', array(
                'required'   => true, 
                'label'      => 'Nombre de vacations par expert',
                'attr'        => array(
                        'class' => 'validate[required,custom[integer],min[0]]'
                )
            ))
            ->add('prestataire', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Prestataire affecté',
                'empty_value'   => ' - ',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'PRESTATAIRE')
                              ->orderBy('ref.libelle', 'ASC');
                },
                'attr'         => array('class' => 'validate[required]')
            ))
            ->add('uniteOeuvreConcerne', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Unité d\'oeuvre concernée',
                'empty_value'   => ' - ',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'UO_PRESTATAIRE')
                              ->orderBy('ref.libelle', 'ASC');
                },
                'attr'         => array('class' => 'validate[required]')
            ))
            ->add('anapiens', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueUserBundle:User',
                    'property'      => 'appellation',
                    'multiple'      => true,
                    'required'      => true,
                    'label'         => 'Anapien(s) référent(s)',
                    'empty_value'   => ' - ',
                    'attr'       => array('class' => 'select2'),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->findUsersByDomaine(1);
                    }
            ))
            ->add('etat', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Etat',
                'empty_value'   => ' - ',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'ACTIVITE_EXPERT_ETAT')
                              ->orderBy('ref.libelle', 'ASC');
                },
                'attr'         => array('class' => 'validate[required]')
            )) 
            // ->add('etatValidation', 'checkbox', array(
            //     'required'   => false, 
            //     'label'      => 'Validation'
            // ))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ExpertBundle\Entity\ActiviteExpert'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_expert_activiteexpert';
    }
}
