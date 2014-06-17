<?php

namespace HopitalNumerique\ModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class AddInscriptionType extends AbstractType
{
    private $_constraints = array();
    
    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roleNames = (isset($options['label_attr']['roleNames']) && !is_null($options['label_attr']['roleNames'])) ? $options['label_attr']['roleNames'] : array();

        $builder
            ->add('commentaire', 'textarea', array(
                    'required' => false,
                    'label'    => 'Informations complémentaires',
                    'attr'     => array(
                        'rows' => 8
                    )
            ))
            ->add('user', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueUserBundle:User',
                    'property'      => 'appellation',
                    'required'      => true,
                    'label'         => 'Utilisateur',
                    'empty_value'   => ' - ',
                    'query_builder' => function(EntityRepository $er) use ($roleNames) {
                        return $er->getUsersByRole($roleNames);
                    }
            ))
            ->add('etatInscription', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => true,
                    'label'         => 'Inscription',
                    'empty_value'   => ' - ',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'STATUT_FORMATION')
                        ->orderBy('ref.order', 'ASC');
                    }
            ))
            ->add('etatParticipation', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => true,
                    'label'         => 'Participation',
                    'empty_value'   => ' - ',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'STATUT_PARTICIPATION')
                        ->orderBy('ref.order', 'ASC');
                    }
            ))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ModuleBundle\Entity\Inscription'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_module_addinscription';
    }
}