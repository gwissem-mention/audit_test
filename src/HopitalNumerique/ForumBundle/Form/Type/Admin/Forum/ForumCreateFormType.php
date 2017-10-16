<?php

/*
 * This file is part of the CCDNForum ForumBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/>
 *
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HopitalNumerique\ForumBundle\Form\Type\Admin\Forum;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @category CCDNForum
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 */
class ForumCreateFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $forumClass;

    /**
     * @var object
     */
    protected $roleHelper;

    /**
     * @param string $forumClass
     * @param object $roleHelper
     */
    public function __construct($forumClass, $roleHelper)
    {
        $this->forumClass = $forumClass;
        $this->roleHelper = $roleHelper;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'validate[required,minSize[3],maxSize[255]]',
                ],
            ])
            ->add('domain', EntityType::class, [
                'class' => Domaine::class,
                'empty_value' => '-',
                'attr' => [
                    'class' => 'validate[required]',
                ],
            ])
            ->add('readAuthorisedRoles', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices' => $options['available_roles'],
            ])
        ;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->forumClass,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'forum_forum_create_item',
            'validation_groups' => ['forum_forum_create'],
            'cascade_validation' => true,
            'available_roles' => $this->roleHelper->getRoleForFormulaire(),
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Forum_ForumCreate';
    }
}
