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

namespace HopitalNumerique\ForumBundle\Form\Type\User\Post;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @category CCDNForum
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 */
class PostUpdateFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $postClass;

    /**
     * @param string $postClass
     */
    public function __construct($postClass)
    {
        $this->postClass = $postClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body', 'bb_editor',
                [
                    'label' => 'post.body-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr' => [
                        'class' => 'validate[required,minSize[15]]',
                        'rows' => 8,
                        'acl_group' => 'default',
                    ],
                ]
            )
        ;
        if ($builder->getData()->getTopic()->getBoard()->isPiecesJointesAutorisees()) {
            $builder
                ->add('pieceJointeFile', 'file',
                    [
                        'required' => false,
                        'label' => 'Pièce jointe :',
                    ]
                )
            ;
        }
        $builder
            ->add('pieceJointeSuppression', (null !== $builder->getData()->getPieceJointe() ? 'checkbox' : 'hidden'),
                [
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Supprimer la pièce jointe actuelle ?',
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
            'data_class' => $this->postClass,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'forum_post_update_item',
            'validation_groups' => ['forum_post_update'],
            'cascade_validation' => true,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Post';
    }
}
