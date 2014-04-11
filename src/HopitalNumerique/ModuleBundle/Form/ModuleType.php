<?php

namespace HopitalNumerique\ModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text', array(
                    'max_length' => $this->_constraints['titre']['maxlength'],
                    'required' => true,
                    'label'    => 'Titre du module',
                    'attr'        => array(
                            'class' => $this->_constraints['titre']['class']
                    ),
            ))
            ->add('productions', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'productions'
            ))
            ->add('duree', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => false,
                    'label'         => 'Durée',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => $this->_constraints['duree']['class'] ),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'DUREE_FORMATION')
                        ->orderBy('ref.order', 'ASC');
                    }
            ))
            ->add('horairesType', 'text', array(
                    'max_length' => $this->_constraints['horairesType']['maxlength'],
                    'required' => false,
                    'label'    => 'Horaires type',
                    'attr'        => array(
                            'class' => $this->_constraints['horairesType']['class']
                    ),
            ))
            ->add('lieu', 'textarea', array(
                    'required' => false,
                    'label'    => 'Lieu',
                    'attr'        => array(
                            'class' => $this->_constraints['lieu']['class'],
                            'rows'   => 3
                    ),
            ))
            ->add('description', 'textarea', array(
                    'required' => false,
                    'label'    => 'Description',
                    'attr'        => array(
                            'class' => $this->_constraints['description']['class'],
                            'rows'   => 3
                    ),
            ))
            ->add('nombrePlaceDisponible', 'integer', array(
                'required'   => false, 
                'label'      => 'Nombre de places disponibles',
            ))
            ->add('prerequis', 'textarea', array(
                    'required' => false,
                    'label'    => 'Prérequis',
                    'attr'        => array(
                            'class' => $this->_constraints['prerequis']['class'],
                            'rows'   => 3
                    ),
            ))
            ->add('path', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'path'
            ))
            ->add('formateur', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'formateur'
            ))
            ->add('statut', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'statut'
            ))        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ModuleBundle\Entity\Module'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_module_module';
    }
}
