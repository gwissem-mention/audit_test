<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use HopitalNumerique\UserBundle\Manager\UserManager;

use Doctrine\ORM\EntityRepository;

class RechercheParcoursGestionType extends AbstractType
{
    private $_userManager;

    public function __construct($manager, $validator, UserManager $userManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->_userManager->getUserConnected();
        $isCreation = (null === $builder->getData()->getId());

        $builder
            ->add('nom', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Nom',
                'attr'        => array('class' => 'validate[required]')
            ))
            ->add('domaines', 'entity', array(
                'class'       => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property'    => 'nom',
                'required'    => false,
                'multiple'    => true,
                'label'       => 'Domaine(s) associé(s)',
                'empty_value' => ' - ',
                'query_builder' => function(EntityRepository $er) use ($connectedUser){
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                }
            ))
            ->add('typePublication', 'genemu_jqueryselect2_entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'    => 'libelle',
                'required'    => true,
                'multiple'    => true,
                'label'       => 'Type de publication à afficher',
                'empty_value' => ' - ',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('ref')
                        ->leftJoin('ref.parents', 'parent')
                        ->where('ref.code = :code')
                        ->andWhere('parent.id IS NULL')
                        ->setParameter('code', 'CATEGORIE_OBJET');
                }
            ))
        ;
        if (!$isCreation) {
            $builder
                ->add('referencesParentes', 'genemu_jqueryselect2_entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => true,
                    'multiple'    => true,
                    'label'       => 'Référence(s) parente(s)',
                    //'group_by'    => 'parent',
                    'empty_value' => ' - ',
                    'query_builder' => function(EntityRepository $er) use ($connectedUser){
                        return $er->getReferencesUserConnectedForForm($connectedUser->getId());
                    }
                ))
                ->add('referencesVentilations', 'genemu_jqueryselect2_entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => true,
                    'multiple'    => true,
                    'label'       => 'Référence(s) de ventilation',
                    //'group_by'    => 'parent',
                    'empty_value' => ' - ',
                    'query_builder' => function(EntityRepository $er) use ($connectedUser){
                        return $er->getReferencesUserConnectedForForm($connectedUser->getId());
                    }
                ))
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_rechercheparcours_rechercheparcoursgestion';
    }
}
