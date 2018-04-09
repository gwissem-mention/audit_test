<?php

namespace HopitalNumerique\ExpertBundle\Form;

use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class ActiviteExpertType
 */
class ActiviteExpertType extends AbstractType
{
    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * ActiviteExpertType constructor.
     *
     * @param ReferenceManager $referenceManager
     */
    public function __construct(ReferenceManager $referenceManager)
    {
        $this->referenceManager = $referenceManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'max_length' => 255,
                'required' => true,
                'label' => 'Titre',
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('typeActivite', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('ACTIVITE_TYPE'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Type d\'activité',
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('dateDebut', 'genemu_jquerydate', [
                'required' => true,
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'validate[required] datepicker'],
            ])
            ->add('dateFin', 'genemu_jquerydate', [
                'required' => false,
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'datepicker'],
            ])
            ->add('expertConcernes', 'genemu_jqueryselect2_entity', [
                'class' => User::class,
                'property' => 'appellation',
                'multiple' => true,
                'required' => true,
                'label' => 'Expert(s) concerné(s)',
                'empty_value' => ' - ',
                'attr' => ['class' => 'select2'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->findUsersByRole('ROLE_EXPERT_6');
                },
            ])
            ->add('nbVacationParExpert', IntegerType::class, [
                'required' => true,
                'label' => 'Nombre de vacations par expert',
                'attr' => [
                    'class' => 'validate[required,custom[integer],min[0]]',
                ],
            ])
            ->add('prestataire', EntityType::class, [
                'class' => Reference::class,
                'property' => 'libelle',
                'required' => true,
                'label' => 'Prestataire affecté',
                'empty_value' => ' - ',
                'choices' => $this->referenceManager->findByCode('PRESTATAIRE'),
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('uniteOeuvreConcerne', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('UO_PRESTATAIRE'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Unité d\'oeuvre concernée',
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('anapiens', 'genemu_jqueryselect2_entity', [
                'class' => User::class,
                'property' => 'appellation',
                'multiple' => true,
                'required' => true,
                'label' => 'Anapien(s) référent(s)',
                'empty_value' => ' - ',
                'attr' => ['class' => 'select2'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->findUsersByDomaine(1);
                },
            ])
            ->add('etat', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('ACTIVITE_EXPERT_ETAT'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Etat',
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ActiviteExpert::class,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_expert_activiteexpert';
    }
}
