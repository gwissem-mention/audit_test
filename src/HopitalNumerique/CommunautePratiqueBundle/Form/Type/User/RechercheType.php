<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Annuaire;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Formulaire d'édition d'un groupe de la communauté de pratique.
 */
class RechercheType extends AbstractType
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface Router
     */
    private $router;

    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Annuaire Service Annuaire
     */
    private $annuaireService;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager 
     */
    private $referenceManager;


    /**
     * Constructeur.
     */
    public function __construct(RouterInterface $router, Annuaire $annuaireService, ReferenceManager $referenceManager)
    {
        $this->router = $router;
        $this->annuaireService = $annuaireService;
        $this->referenceManager = $referenceManager;
    }


    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->router->generate('hopitalnumerique_communautepratique_user_list'))
            ->add('nom', 'text', array(
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_NOMINATION_LABEL),
                'attr' => array(
                    'placeholder' => 'Rechercher un nom, un prénom'
                )
            ))
            ->add('profilEtablissementSante', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findEtablissementSanteProfils(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_ES_PROFIL_LABEL),
                'multiple' => true
            ))
            ->add('region', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findRegions(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_REGION_LABEL),
                'multiple' => true
            ))
            ->add('statutEtablissementSante', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findEtablissementSanteTypes(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_ES_TYPE_LABEL),
                'multiple' => true
            ))
            ->add('typeActivite', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findActiviteTypes(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_ACTIVITE_TYPE_LABEL),
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
