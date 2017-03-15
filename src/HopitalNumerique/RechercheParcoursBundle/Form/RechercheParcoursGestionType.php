<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $isCreation = (null === $builder->getData()->getId());

        $builder
            ->add('nom', TextType::class, [
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
            ->add('typePublication', 'genemu_jqueryselect2_entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => true,
                'multiple' => true,
                'label' => 'Type de publication à afficher',
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                        ->leftJoin('ref.parents', 'parent')
                        ->leftJoin('ref.codes', 'codes')
                        ->where('codes.label = :code')
                        ->andWhere('parent.id IS NULL')
                        ->setParameter('code', 'CATEGORIE_OBJET');
                },
            ])
        ;
        if (!$isCreation) {
            $domaines = $this->entity->getEntityDomainesCommunsWithUser($builder->getData(), $connectedUser);
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
            'data_class' => RechercheParcoursGestion::class,
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_rechercheparcours_rechercheparcoursgestion';
    }
}
