<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @var RouterInterface Router
     */
    private $router;

    /**
     * @var Annuaire Service Annuaire
     */
    private $annuaireService;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * Constructeur.
     *
     * @param RouterInterface  $router
     * @param Annuaire         $annuaireService
     * @param ReferenceManager $referenceManager
     */
    public function __construct(RouterInterface $router, Annuaire $annuaireService, ReferenceManager $referenceManager)
    {
        $this->router = $router;
        $this->annuaireService = $annuaireService;
        $this->referenceManager = $referenceManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->router->generate('hopitalnumerique_communautepratique_user_list'));
        $builder
            ->add('id', HiddenType::class, [
                'attr' => [
                    'placeholder' => 'Rechercher un id',
                ],
            ])
            ->add('nom', TextType::class, [
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_NOMINATION_LABEL),
                'attr' => [
                    'placeholder' => 'Rechercher un nom, un prénom',
                ],
            ])
            ->add('profileType', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findEtablissementSanteProfils(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_ES_PROFIL_LABEL),
                'multiple' => true,
            ])
            ->add('region', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findRegions(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_REGION_LABEL),
                'multiple' => true,
            ])
            ->add('organizationType', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findEtablissementSanteTypes(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_ES_TYPE_LABEL),
                'multiple' => true,
            ])
            ->add('activities', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'choices' => $this->referenceManager->findActiviteTypes(),
                'data' => $this->annuaireService->getFiltre(Annuaire::FILTRE_ACTIVITE_TYPE_LABEL),
                'multiple' => true,
            ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_user_recherche';
    }
}
