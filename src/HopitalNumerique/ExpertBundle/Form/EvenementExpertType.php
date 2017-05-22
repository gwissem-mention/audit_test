<?php

namespace HopitalNumerique\ExpertBundle\Form;

use HopitalNumerique\ExpertBundle\Entity\EvenementExpert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementExpertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => true,
                'label' => 'Type d\'évènement',
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                        ->leftJoin('ref.codes', 'codes')
                        ->where('codes.label = :etat')
                        ->setParameter('etat', 'TYPE_EVENEMENT')
                        ->orderBy('ref.libelle', 'ASC')
                    ;
                },
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('nbVacation', IntegerType::class, [
                'required' => true,
                'label' => 'Nombre de vacations',
                'attr' => [
                        'class' => 'validate[required,custom[integer],min[0]]',
                ],
            ])
            ->add('date', 'genemu_jquerydate', [
                'required' => true,
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'validate[required] datepicker'],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvenementExpert::class,
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_expert_evenementexpert';
    }
}
