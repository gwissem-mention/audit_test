<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

/**
 * ModelType.
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => ['class' => 'validate[required,max[255]]'],
            ])
            ->add('instructions')
            ->add('partialResultsAuthorized')
            ->add('comparisonAuthorized')
            ->add('synthesisAuthorized')
            ->add('domaines', EntityType::class, [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'required' => true,
                'multiple' => true,
                'empty_value' => ' - ',
                'disabled' => $options['edit'],
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->getDomainesUserConnectedForForm($options['user']->getId());
                },
                'attr' => [
                    'class' => 'select2 validate[required,minSize[3],maxSize[255]]',
                ],
            ])
            ->add('questionnaire', EntityType::class, [
                'class' => Questionnaire::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('questionnaire')
                        ->where('questionnaire.occurrenceMultiple = false');
                },
                'required' => false,
            ])
            ->add('published', CheckboxType::class, [
                'label' => "Publié",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Autodiag',
            'label_format' => 'ad.autodiag.%name%',
            'edit' => false,
        ]);

        $resolver->setRequired(['user', 'edit']);
        $resolver->setAllowedTypes('user', User::class);
        $resolver->setAllowedTypes('edit', 'boolean');
    }
}
