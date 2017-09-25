<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type\Discussion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\CreateDiscussionCommand;

class CreateDiscussionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateDiscussionCommand::class,
            'label_format' => 'discussion.new_discussion.form.%name%.label',
            'translation_domain' => 'cdp_discussion',
        ]);
    }
}
