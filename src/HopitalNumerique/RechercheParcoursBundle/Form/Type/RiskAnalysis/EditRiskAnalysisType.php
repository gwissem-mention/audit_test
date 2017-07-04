<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form\Type\RiskAnalysis;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;

class EditRiskAnalysisType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('probability', IntegerType::class)
            ->add('impact', IntegerType::class)
            ->add('initialSkillsRate', IntegerType::class)
            ->add('currentSkillsRate', IntegerType::class)
            ->add('comment', TextareaType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $event->getForm()->add('excludedObjects', EntityType::class, [
                'class' => Objet::class,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) use ($event) {
                    return $er->createQueryBuilder('o')
                        ->join('o.relatedRisks', 'rr')
                        ->join('rr.risk', 'r', Join::WITH, 'r.id = :riskId')
                        ->setParameter('riskId', $event->getData()->getRisk()->getId())
                    ;
                }
            ]);
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RiskAnalysis::class,
            'csrf_protection' => false,
            'required' => false,
        ]);
    }
}
