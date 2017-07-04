<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\EditGuidedSearchConfigCommand;

class RechercheParcoursGestionType extends AbstractType
{
    /**
     * @var Entity Entity
     */
    private $entity;

    private $_userManager;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    public function __construct(
        $manager,
        $validator,
        Entity $entity,
        UserManager $userManager,
        ReferenceManager $referenceManager
    ) {
        $this->_constraints = $manager->getConstraints($validator);
        $this->entity = $entity;
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->_userManager->getUserConnected();

        $builder
            ->add('name', TextType::class, [
                'max_length' => 255,
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('domaines', EntityType::class, [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property' => 'nom',
                'required' => true,
                'multiple' => true,
                'label' => 'Domaine(s) associé(s)',
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                },
            ])
            ->add('publicationsType', CollectionType::class, [
                'type' => RechercheParcoursGestionPublicationTypeType::class,
                'required' => false,
                'label' => 'Type de publication à afficher',
            ])
        ;
        if ($builder->getData()->update) {
            $domaines = $this->entity->getDomainesCommunsWithUser($builder->getData()->domaines, $connectedUser);
            $references = $this->referenceManager->findByDomaines($domaines, true, null, null, true);

            $builder
                ->add('referencesParentes', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $references,
                    'property' => 'libelle',
                    'required' => true,
                    'multiple' => true,
                    'label' => 'Référence(s) parente(s)',
                    //'group_by'    => 'parent',
                    'empty_value' => ' - ',
                ])
                ->add('referencesVentilations', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $references,
                    'property' => 'libelle',
                    'required' => true,
                    'multiple' => true,
                    'label' => 'Référence(s) de ventilation',
                    //'group_by'    => 'parent',
                    'empty_value' => ' - ',
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EditGuidedSearchConfigCommand::class,
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_rechercheparcours_rechercheparcoursgestion';
    }
}
