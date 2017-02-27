<?php

namespace HopitalNumerique\ExpertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class EvenementExpertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => true,
                'label' => 'Type d\'évènement',
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'TYPE_EVENEMENT')
                              ->orderBy('ref.libelle', 'ASC');
                },
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('nbVacation', 'integer', [
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ExpertBundle\Entity\EvenementExpert',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_expert_evenementexpert';
    }
}
