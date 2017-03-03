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
 * @category CCDNForum
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 */
class TopicCreateFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $topicClass;

    /**
     * @var string
     */
    public function __construct($topicClass)
    {
        $this->topicClass = $topicClass;
    }

    /**
     * @param FormBuilderInterface $builder, array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('board', 'entity',
                [
                    'property' => 'name',
                    'class' => 'CCDNForumForumBundle:Board',
                    'choices' => $options['boards'],
                    'label' => 'board.label',
                    'translation_domain' => 'CCDNForumForumBundle',
                ]
            )
            ->add('title', null,
                [
                    'label' => 'topic.title-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr' => [
                        'class' => 'validate[required,maxSize[255]]',
                    ],
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
            'data_class' => $this->topicClass,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'forum_topic_create_item',
            'validation_groups' => ['forum_topic_custom', 'forum_post_create'],
            'boards' => [],
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Topic';
    }
}
