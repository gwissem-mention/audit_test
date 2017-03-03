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

namespace HopitalNumerique\ForumBundle\Form\Type\Admin\Category;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @category CCDNForum
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 */
class CategoryUpdateFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $categoryClass;

    /**
     * @var string
     */
    protected $forumClass;

    /**
     * @var object
     */
    protected $roleHelper;

    /**
     * @param string $categoryClass
     * @param string $forumClass
     * @param object $roleHelper
     */
    public function __construct($categoryClass, $forumClass, $roleHelper)
    {
        $this->categoryClass = $categoryClass;
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
            ->add('forum', 'entity',
                [
                    'property' => 'name',
                    'class' => $this->forumClass,
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                                ->createQueryBuilder('f')
                            ;
                    },
                    'required' => false,
                    'label' => 'forum.label',
                    'translation_domain' => 'CCDNForumForumBundle',
                ]
            )
            ->add('name', 'text',
                [
                    'label' => 'category.name-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr' => [
                        'class' => 'validate[required,minSize[3],maxSize[255]]',
                    ],
                ]
            )
            ->add('readAuthorisedRoles', 'choice',
                [
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $options['available_roles'],
                    'label' => 'category.roles.board-view-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                ]
            )
        ;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->categoryClass,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'forum_category_update_item',
            'validation_groups' => ['forum_category_update'],
            'cascade_validation' => true,
            'available_roles' => $this->roleHelper->getRoleForFormulaire(),
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Forum_CategoryUpdate';
    }
}
