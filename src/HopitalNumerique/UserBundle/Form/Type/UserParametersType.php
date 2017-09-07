<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use HopitalNumerique\UserBundle\Domain\Command\UpdateUserParametersCommand;

class UserParametersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', PasswordType::class)
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'form.newPassword.first.label',
                ],
                'second_options' => [
                    'label' => 'form.newPassword.second.label',
                ],
            ])
            ->add('publicationNotification', CheckboxType::class, [
                'required' => false,
            ])
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
            'data_class' => UpdateUserParametersCommand::class,
            'label_format' => 'form.%name%.label',
            'translation_domain' => 'user-parameters',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $groups = ['Default'];

                if (!is_null($data->newPassword)) {
                    $groups[] = 'changePassword';
                }

                return $groups;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_new_account_user_parameters';
    }
}
