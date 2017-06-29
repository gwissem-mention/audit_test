<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationType
 */
class RegistrationType extends AbstractType
{
    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * RegistrationType constructor.
     *
     * @param ReferenceManager     $referenceManager
     */
    public function __construct(
        ReferenceManager $referenceManager
    ) {
        $this->referenceManager     = $referenceManager;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param array                $options Data passée au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentResponse = $builder->getData();

        $builder
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => 'password',
                'invalid_message' => 'Ces deux champs doivent être identiques.',
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
            ])
            ->add('region', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('REGION'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Région',
                'empty_value' => ' - ',
            ])
            ->add('county', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Département',
                'empty_value' => ' - ',
            ])
            ->add('organizationType', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Type de structure',
                'empty_value' => ' - ',
            ])
            ->add('organizationLabel', TextType::class, [
                'required' => false,
                'label' => 'Nom de votre structure si non disponible dans la liste précédente',
            ])
            ->add('activities', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('CONTEXTE_SPECIALITE_ES'),
                'choice_label' => 'libelle',
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => 'Type activité (pour les établissements sanitaires)',
                'empty_value' => ' - ',
            ])
            ->add('profileType', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Profil',
                'empty_value' => ' - ',
            ])
            ->add('termsAccepted', CheckboxType::class, [
                'label' => 'J\'accepte les conditions générales d\'utilisation de la plateforme',
            ])
            ->add('etat', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('ETAT'),
                'choice_label' => 'libelle',
                'label' => 'Etat',
            ])
            ->add('inscritCommunautePratique', CheckboxType::class, [
                'label' => 'Membre de la communauté de pratique',
            ])
        ;

        $organizationFieldOptions = [
            'class'        => Etablissement::class,
            'choice_label' => 'appellation',
            'required'     => false,
            'label'        => 'Structure de rattachement',
            'multiple'     => false,
            'empty_value'  => '-',
            'data'         => is_null($currentResponse) ? null : $currentResponse->getOrganization(),
        ];

        $organizationModifier = function (
            FormInterface $form,
            $etabId = null
        ) use (
            $currentResponse,
            $organizationFieldOptions
        ) {

            if (!is_null($etabId)) {
                $organizationFieldOptions['query_builder'] = function (EntityRepository $er) use ($etabId) {
                    return $er->createQueryBuilder('eta')
                        ->andWhere('eta.id = :etabId')->setParameter('etabId', $etabId)
                        ->orderBy('eta.nom', 'ASC')
                    ;
                };
            } else {
                $organizationFieldOptions['choices'] =
                    is_null($currentResponse) || is_null($currentResponse->getOrganization())
                        ? []
                        : [$currentResponse->getOrganization()]
                ;
            }

            $form->add('organization', EntityType::class, $organizationFieldOptions);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($organizationModifier) {
                $organizationModifier($event->getForm());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($organizationModifier) {
                $organizationModifier(
                    $event->getForm(),
                    isset($event->getData()['organization']) ? $event->getData()['organization'] : null
                );
            }
        );
    }

    /**
     * Retourne le nom du formulaire.
     *
     * @return string Nom du formulaire
     */
    public function getName()
    {
        return 'nodevo_user_registration';
    }
}
