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
 *
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 *
 */
class PostCreateFormType extends AbstractType
{
    /**
     *
     * @access protected
     * @var string $postClass
     */
    protected $postClass;

    /**
     *
     * @access public
     * @param string $postClass
     */
    public function __construct($postClass)
    {
        $this->postClass = $postClass;
    }

    /**
     *
     * @access public
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body', 'bb_editor',
                array(
                    'label'              => 'post.body-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr'               => array(
                        'class'     => 'validate[required,minSize[15]]',
                        'rows'      => 8,
                        'acl_group' => 'default'
                    )
                )
            )
            ->add('subscribe', 'checkbox',
                array(
                    'mapped'             => false,
                    'required'           => false,
                    'label'              => 'post.subscribe-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr'     => array(
                        'checked' => 'checked',
                        'class'   => 'checkbox'
                    )
                )
            )
        ;
        if ($builder->getData()->getTopic()->getBoard()->isPiecesJointesAutorisees())
        {
            $builder
                ->add('pieceJointeFile', 'file',
                    array(
                        'required'           => false,
                        'label'              => 'Pièce jointe :'
                    )
                )
            ;
        }
        $builder
            ->add('pieceJointeSuppression', (null !== $builder->getData()->getPieceJointe() ? 'checkbox' : 'hidden'),
                array(
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Supprimer la pièce jointe actuelle ?'
                )
            )
        ;
    }

    /**
     *
     * @access public
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'          => $this->postClass,
            'csrf_protection'     => true,
            'csrf_field_name'     => '_token',
            // a unique key to help generate the secret token
            'intention'           => 'forum_post_create_item',
            'validation_groups'   => array('forum_post_create'),
            'cascade_validation'  => true
        ));
    }

    /**
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return 'Post';
    }
}
