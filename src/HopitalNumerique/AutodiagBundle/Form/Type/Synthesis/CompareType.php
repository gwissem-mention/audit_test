<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class CompareType extends AbstractType
{
    /** @var SynthesisRepository */
    protected $synthesisRepository;

    /** @var RouterInterface */
    protected $router;

    /**
     * ComparisonType constructor.
     * @param SynthesisRepository $synthesisRepository
     * @param RouterInterface $router
     */
    public function __construct(SynthesisRepository $synthesisRepository, RouterInterface $router)
    {
        $this->synthesisRepository = $synthesisRepository;
        $this->router = $router;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', EntityType::class, [
                'empty_value' => '-',
                'class' => Synthesis::class,
                'label' => 'ad.compare.reference',
                'choice_label' => 'name',
                'choices' => $this->synthesisRepository->findComparable($options['user'], $options['domaine']),
                'group_by' => function ($val) {
                    if ($val instanceof Synthesis) {
                        return $val->getAutodiag()->getTitle();
                    }

                    return null;
                },
            ])
        ;

        $addSynthesisType = function (FormInterface $form, Synthesis $reference = null) use ($options) {
            $choices = [];
            if (null !== $reference) {
                $choices = $this->synthesisRepository->findComparableWith(
                    $reference,
                    $options['user'],
                    $options['domaine']
                );
            }

            $form->add('synthesis', EntityType::class, [
                'empty_value' => '-',
                'class' => Synthesis::class,
                'label' => 'ad.compare.synthesis',
                'choice_label' => 'name',
                'choices' => $choices,
                'group_by' => function ($val) {
                    if ($val instanceof Synthesis) {
                        return $val->getAutodiag()->getTitle();
                    }

                    return null;
                },
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($addSynthesisType) {
                $data = $event->getData();
                $addSynthesisType($event->getForm(), $data->reference);
            }
        );

        $builder->get('reference')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($addSynthesisType) {
                $reference = $event->getForm()->getData();
                $addSynthesisType($event->getForm()->getParent(), $reference);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\AutodiagBundle\Model\Synthesis\CompareCommand',
            'label_format' => 'ad.comparison.%name%',
            'domaine' => null,
            'action' => $this->router->generate('hopitalnumerique_autodiag_synthesis_compare'),
        ]);

        $resolver->setDefined('user');
        $resolver->addAllowedTypes('user', User::class);
        $resolver->setRequired(['user']);

        $resolver->setDefined('domaine');
        $resolver->addAllowedTypes('domaine', ['null', Domaine::class]);
    }
}
