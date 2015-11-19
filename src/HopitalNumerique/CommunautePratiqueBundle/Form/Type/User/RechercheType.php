<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Formulaire d'édition d'un groupe de la communauté de pratique.
 */
class RechercheType extends AbstractType
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager 
     */
    private $referenceManager;


    /**
     * Constructeur.
     */
    public function __construct(ReferenceManager $referenceManager)
    {
        $this->referenceManager = $referenceManager;
    }


    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
                'attr' => array(
                    'placeholder' => 'Rechercher un nom, un prénom'
                )
            ))
            ->add('profilEtablissementSante', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findEtablissementSanteProfils(),
                'multiple' => true
            ))
            ->add('region', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findRegions(),
                'multiple' => true
            ))
            ->add('statutEtablissementSante', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findEtablissementSanteTypes(),
                'multiple' => true
            ))
            ->add('typeActivite', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findActiviteTypes(),
                'multiple' => true
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_user_recherche';
    }
}
