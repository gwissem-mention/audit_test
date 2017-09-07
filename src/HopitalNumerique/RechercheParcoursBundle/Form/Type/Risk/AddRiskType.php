<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\AddPrivateRiskCommand;

class AddRiskType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nature', EntityType::class, [
                'class' => Reference::class,
                'choice_label' => 'libelle',
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('r')
                        ->join('r.codes', 'c', Join::WITH, 'c.label = :code')
                        ->setParameter('code', 'NATURE_DU_RISQUE')
                    ;
                }
            ])
            ->add('label', TextType::class)
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddPrivateRiskCommand::class,
        ]);
    }
}
