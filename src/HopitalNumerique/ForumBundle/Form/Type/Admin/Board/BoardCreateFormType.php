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

namespace HopitalNumerique\ForumBundle\Form\Type\Admin\Board;

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
class BoardCreateFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $boardClass;

    /**
     * @var string
     */
    protected $categoryClass;

    /**
     * @var object
     */
    protected $roleHelper;

    /**
     * @param string $boardClass
     * @param string $categoryClass
     * @param object $roleHelper
     */
    public function __construct($boardClass, $categoryClass, $roleHelper)
    {
        $this->boardClass = $boardClass;
        $this->categoryClass = $categoryClass;
        $this->roleHelper = $roleHelper;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', 'entity',
                [
                    'property' => 'name',
                    'class' => $this->categoryClass,
                    'group_by' => 'forum.name',
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                                ->createQueryBuilder('c')
                                ->leftJoin('c.forum', 'f')
                                //->groupBy('c.forum')
                            ;
                    },
                    'data' => $options['default_category'],
                    'required' => false,
                    'label' => 'category.label',
                    'translation_domain' => 'CCDNForumForumBundle',
                ]
            )
            ->add('name', 'text',
                [
                    'label' => 'board.name-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr' => [
                        'class' => 'validate[required,minSize[3],maxSize[255]]',
                    ],
                ]
            )
            ->add('description', 'textarea',
                [
                    'label' => 'board.description-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr' => [
                        'class' => 'validate[required,minSize[10],maxSize[255]]',
                    ],
                ]
            )
            ->add('piecesJointesAutorisees', 'checkbox',
                [
                    'label' => 'Autoriser le chargement d\'une pièce jointe ?',
                    'required' => false,
                ]
            )
            ->add('readAuthorisedRoles', 'choice',
                [
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $options['available_roles'],
                    'label' => 'board.roles.topic-view-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                ]
            )
            ->add('topicCreateAuthorisedRoles', 'choice',
                [
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $options['available_roles'],
                    'label' => 'board.roles.topic-create-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                ]
            )
            ->add('topicReplyAuthorisedRoles', 'choice',
                [
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $options['available_roles'],
                    'label' => 'board.roles.topic-reply-label',
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
            'data_class' => $this->boardClass,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'forum_board_create_item',
            'validation_groups' => ['forum_board_create'],
            'cascade_validation' => true,
            'available_roles' => $this->roleHelper->getRoleForFormulaire(),
            'default_category' => null,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Forum_BoardCreate';
    }
}
