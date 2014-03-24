<?php

namespace Nodevo\RoleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class RoleType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'max_length' => $this->_constraints['name']['maxlength'], 
                'required'   => true, 
                'label'      => 'Nom',
                'attr'       => array('class' => $this->_constraints['name']['class'] )
            ));

        //On peut uniquement modifier l'état des groupes NON initiaux
        if( ! $options['data']->getInitial() ) {
            $builder
                ->add('etat', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => true,
                    'label'       => 'Etat',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                                  ->where('ref.code = :etat')
                                  ->setParameter('etat', 'ETAT')
                                  ->orderBy('ref.order', 'ASC');
                    }
                ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nodevo\RoleBundle\Entity\Role'
        ));
    }

    public function getName()
    {
        return 'nodevo_role_role';
    }
}
