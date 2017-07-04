<?php

namespace HopitalNumerique\ObjetBundle\Form;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\ObjetBundle\Domain\Command\EditRiskCommand;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Repository\RiskRepository;
use Symfony\Component\Form\AbstractType;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RiskType extends AbstractType
{
    /**
     * @var RiskRepository $riskRepository
     */
    protected $riskRepository;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * RiskType constructor.
     *
     * @param RiskRepository $riskRepository
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(RiskRepository $riskRepository, TokenStorageInterface $tokenStorage)
    {
        $this->riskRepository = $riskRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $builder->getData()->author;

        $builder
            ->add('label', TextType::class)
            ->add('archived', CheckboxType::class)
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
            ->add('domains', EntityType::class, [
                'class' => Domaine::class,
                'multiple' => true,
                'choices' => $this->tokenStorage->getToken()->getUser()->getDomaines(),
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var EditRiskCommand $command */
            $command = $event->getData();
            $form = $event->getForm();

            if (!is_null($command->riskId)) {
                /** @var Risk $risk */
                $risk = $this->riskRepository->find($command->riskId);

                $form
                    ->add('fusionTarget', EntityType::class, [
                        'class' => Risk::class,
                        'query_builder' => $this->riskRepository->createRelatedRisksQueryBuilder($risk),
                        'required' => false,
                        'group_by' => 'nature.libelle',
                    ])
                    ->add('confirmFusion', CheckboxType::class, [
                        'required' => false,
                    ])
                ;
            }

            if ($command->private) {
                $form
                    ->add('publish', CheckboxType::class, [
                        'required' => false,
                    ])
                ;
            }
        });
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ObjetBundle\Domain\Command\EditRiskCommand',
            'translation_domain' => 'risk',
            'label_format' => 'edit.form.%name%.label',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_objetbundle_risk';
    }
}
