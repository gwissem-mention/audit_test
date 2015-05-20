<?php

namespace HopitalNumerique\RechercheBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use HopitalNumerique\UserBundle\Manager\UserManager;

use Doctrine\ORM\EntityRepository;

class ExpBesoinGestionType extends AbstractType
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

        $builder
            ->add('nom', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'nom'
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
          ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_recherche_expbesoingestion';
    }
}
