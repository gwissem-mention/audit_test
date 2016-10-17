<?php
namespace HopitalNumerique\AutodiagBundle\Form\Type\Autodiag;

use HopitalNumerique\AutodiagBundle\Form\Type\AutodiagType;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ModelAdminUpdateType represent the admin form for create and update Model
 *
 * @package HopitalNumerique\AutodiagBundle\Form\Type\Domain
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagUpdateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('autodiag', AutodiagType::class, [
                'user' => $options['user'],
                'edit' => $options['edit'],
            ])
            ->add('presets', CollectionType::class, [
                'entry_type' => PresetType::class,
                'entry_options' => [
                    'error_bubbling' => false,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\AutodiagBundle\Model\AutodiagUpdate',
            'label_format' => 'ad.autodiag.%name%',
            'edit' => false,
        ]);

        $resolver->setRequired(['user', 'edit']);
        $resolver->setAllowedTypes('user', User::class);
        $resolver->setAllowedTypes('edit', 'boolean');
    }
}
