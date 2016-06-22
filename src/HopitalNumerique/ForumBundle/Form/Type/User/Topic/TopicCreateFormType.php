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

namespace HopitalNumerique\ForumBundle\Form\Type\User\Topic;

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
class TopicCreateFormType extends AbstractType
{
    /**
     *
     * @access protected
     * @var string $topicClass
     */
    protected $topicClass;

    /**
     *
     * @access public
     * @var string $topicClass
     */
    public function __construct($topicClass)
    {
        $this->topicClass = $topicClass;
    }

    /**
     *
     * @access public
     * @param FormBuilderInterface $builder, array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('board', 'entity',
                array(
                    'property'           => 'name',
                    'class'              => 'CCDNForumForumBundle:Board',
                    'choices'            => $options['boards'],
                    'label'              => 'board.label',
                    'translation_domain' => 'CCDNForumForumBundle',
                )
            )
            ->add('title', null,
                array(
                    'label'              => 'topic.title-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr'               => array(
                        'class' => 'validate[required,maxSize[255]]'
                    )
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
            'data_class'         => $this->topicClass,
            'csrf_protection'    => true,
            'csrf_field_name'    => '_token',
            // a unique key to help generate the secret token
            'intention'          => 'forum_topic_create_item',
            'validation_groups'  => array('forum_topic_custom', 'forum_post_create'),
            'boards'             => array(),
        ));
    }

    /**
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return 'Topic';
    }
}
