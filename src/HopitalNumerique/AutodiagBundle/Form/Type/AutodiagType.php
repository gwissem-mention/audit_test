<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ModelType
 *
 * @package HopitalNumerique\AutodiagBundle\Form\Type
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('instructions')
            ->add('partialResultsAuthorized')
            ->add('synthesisAuthorized')
            ->add('domaines', EntityType::class, [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'required' => true,
                'multiple' => true,
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->getDomainesUserConnectedForForm($options['user']->getId());
                },
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('questionnaire', EntityType::class, [
                'class' => Questionnaire::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('questionnaire')
                        ->where('questionnaire.occurrenceMultiple = false');
                },
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Autodiag',
            'label_format' => 'ad.autodiag.%name%',
        ]);

        $resolver->setRequired(['user']);
        $resolver->setAllowedTypes('user', User::class);
    }
}
