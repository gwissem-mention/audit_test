<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type\Discussion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\PostDiscussionMessageCommand;

class DiscussionMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class)
            ->add('files', CollectionType::class, [
                'type' => HiddenType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PostDiscussionMessageCommand::class,
            'label_format' => 'discussion.message.answer.form.%name%.label',
            'translation_domain' => 'cdp_discussion',
        ]);
    }
}
