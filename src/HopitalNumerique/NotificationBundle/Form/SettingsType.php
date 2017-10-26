<?php

namespace HopitalNumerique\NotificationBundle\Form;

use Doctrine\DBAL\Types\TextType;
use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\NotificationBundle\Service\Provider\FrequenciesBlacklistInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsType
 */
class SettingsType extends AbstractType
{
    /**
     * @var Notifications
     */
    protected $notifications;

    /**
     * @param Notifications $notifications
     */
    public function __construct(Notifications $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $frequencies = [
            NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT => 'form.straight',
            NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_DAILY => 'form.daily',
            NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_WEEKLY => 'form.weekly',
        ];

        $builder
            ->add('wanted', CheckboxType::class)
            ->add('detailLevel', CheckboxType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($frequencies) {
            $form = $event->getForm();
            $provider = $this->notifications->getProvider($event->getData()->getNotificationCode());

            if ($provider instanceof FrequenciesBlacklistInterface) {
                $frequencies = array_diff_key(
                    $frequencies,
                    array_flip($provider->getForbiddenFrequencies())
                );
            }

            $form->add('frequency', ChoiceType::class, [
                'choices' => $frequencies,
                'choice_translation_domain' => 'notifications',
            ]);
        });

        return $builder;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
        ]);
    }
}
