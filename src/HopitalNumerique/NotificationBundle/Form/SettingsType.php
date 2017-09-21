<?php

namespace HopitalNumerique\NotificationBundle\Form;

use Doctrine\DBAL\Types\TextType;
use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
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
        $builder
            ->add('wanted', CheckboxType::class)
            ->add('frequency', ChoiceType::class, [
                'choices' => [
                    '',
                    'daily',
                    'weekly',
                    'straight',
                ],
                'choices_as_values' => true,
                'choice_label' => function ($value, $key) {
                    $value = $key === 0 ? 'choose' : $value;
                    return 'form.'.$value;
                },
                'choice_translation_domain' => 'notifications'
            ])
            ->add('detailLevel', CheckboxType::class, [

            ]);
        return $builder;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
        ]);
    }
}
