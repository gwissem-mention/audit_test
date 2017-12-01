<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use HopitalNumerique\UserBundle\Domain\Command\UpdateNotificationsSettingsCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use HopitalNumerique\NotificationBundle\Form\SettingsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class NotificationsSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notificationsSettings', CollectionType::class, [
                'entry_type' => SettingsType::class
            ])
            ->add('scheduleDay', HiddenType::class)
            ->add('scheduleHour', HiddenType::class)
            ->add('activityNewsletter', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => UpdateNotificationsSettingsCommand::class,
            'label_format' => 'form.%name%.label',
            'translation_domain' => 'user-parameters',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_new_account_notifications_settings';
    }
}
