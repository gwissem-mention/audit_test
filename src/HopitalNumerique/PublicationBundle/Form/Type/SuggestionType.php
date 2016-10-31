<?php

namespace HopitalNumerique\PublicationBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuggestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr'       => array('class' => 'validate[required,max[255]]')
            ])
            ->add('creationDate', 'date', [
                'widget' => 'single_text',
            ])
            ->add('domains', EntityType::class, [
                'class' => Domaine::class,
                'required' => true,
                'multiple' => true,
                'empty_value' => ' - ',
//                'query_builder' => function (EntityRepository $er) use ($options) {
//                    return $er->getDomainesUserConnectedForForm($options['user']->getId());
//                },
                'attr' => [
                    'class' => 'select2 validate[required,minSize[3],maxSize[255]]'
                ]
            ])
            ->add('state', EntityType::class, [
                'class' => Reference::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('reference')
                        ->where("reference.code = 'ETAT_SUGGESTION'");
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
//        $resolver->setDefaults([
//            'data_class' => 'HopitalNumerique\PublicationBundle\Entity\Suggestion',
//            'label_format' => 'ad.autodiag.%name%',
//            'edit' => false,
//        ]);
//
//        $resolver->setRequired(['user', 'edit']);
//        $resolver->setAllowedTypes('user', User::class);
//        $resolver->setAllowedTypes('edit', 'boolean');
    }
}
