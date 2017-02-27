<?php

namespace HopitalNumerique\ExpertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Doctrine\ORM\EntityRepository;

class ActiviteExpertType extends AbstractType
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    public function __construct(ReferenceManager $referenceManager)
    {
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text', [
                'max_length' => 255,
                'required' => true,
                'label' => 'Titre',
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('typeActivite', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
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
                'required' => true,
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'validate[required] datepicker'],
            ])
            ->add('expertConcernes', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueUserBundle:User',
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
            ->add('nbVacationParExpert', 'integer', [
                'required' => true,
                'label' => 'Nombre de vacations par expert',
                'attr' => [
                        'class' => 'validate[required,custom[integer],min[0]]',
                ],
            ])
            ->add('prestataire', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => true,
                'label' => 'Prestataire affecté',
                'empty_value' => ' - ',
                'choices' => $this->referenceManager->findByCode('PRESTATAIRE'),
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('uniteOeuvreConcerne', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('UO_PRESTATAIRE'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Unité d\'oeuvre concernée',
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('anapiens', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueUserBundle:User',
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
            ->add('etat', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('ACTIVITE_EXPERT_ETAT'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Etat',
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
            ])
            // ->add('etatValidation', 'checkbox', array(
            //     'required'   => false,
            //     'label'      => 'Validation'
            // ))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ExpertBundle\Entity\ActiviteExpert',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_expert_activiteexpert';
    }
}
