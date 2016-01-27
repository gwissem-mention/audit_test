<?php

namespace HopitalNumerique\GlossaireBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

use Doctrine\ORM\EntityRepository;

class GlossaireType extends AbstractType
{
    private $_constraints = array();
    private $_userManager;
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    public function __construct($manager, $validator, UserManager $userManager, ReferenceManager $referenceManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->_userManager->getUserConnected();

        $builder
            ->add('mot', 'text', array(
                'max_length' => $this->_constraints['mot']['maxlength'],
                'required'   => true, 
                'label'      => 'Mot',
                'attr'       => array('class' => $this->_constraints['mot']['class'] )
            ))
            ->add('intitule', 'textarea', array(
                'required'   => false, 
                'label'      => 'Intitulé'
            ))
            ->add('description', 'textarea', array(
                'required'   => false, 
                'label'      => 'Description complète',
                'attr'     => array('class' => 'tinyMce')
            ))
            ->add('sensitive', 'checkbox', array(
                'required'   => false,
                'label'      => 'Case sensitive',
                'attr'       => array( 'class'=> 'checkbox', 'style' => 'padding:0; margin:8px 0 0 0' )
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
            ->add('etat', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices'       => $this->referenceManager->findByCode('ETAT'),
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Etat',
                'attr'          => array('class' => $this->_constraints['etat']['class'] ),
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\GlossaireBundle\Entity\Glossaire'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_glossaire_glossaire';
    }
}
