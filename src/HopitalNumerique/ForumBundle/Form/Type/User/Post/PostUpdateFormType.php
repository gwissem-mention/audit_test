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

use HopitalNumerique\ForumBundle\Manager\TopicManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 *
 */
class PostUpdateFormType extends AbstractType
{
    /**
     *
     * @access protected
     * @var string $postClass
     */
    protected $postClass;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \HopitalNumerique\ForumBundle\Manager\TopicManager TopicManager
     */
    private $topicManager;


    /**
     *
     * @access public
     * @param string $postClass
     */
    public function __construct($postClass, AuthorizationCheckerInterface $authorizationChecker, TopicManager $topicManager)
    {
        $this->postClass = $postClass;
        $this->authorizationChecker = $authorizationChecker;
        $this->topicManager = $topicManager;
    }


    /**
     *
     * @access public
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $forum = $builder->getData()->getTopic()->getBoard()->getCategory()->getForum();

        if ($this->authorizationChecker->isGranted('ROLE_ADMINISTRATEUR_1')
            || $this->authorizationChecker->isGranted('ROLE_ADMINISTRATEUR_DE_DOMAINE_106')
            || $this->authorizationChecker->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107')) {
            $builder
                ->add(
                    'topic',
                    'entity',
                    array(
                        'class' => 'HopitalNumeriqueForumBundle:Topic',
                        'choices' => $this->topicManager->findByForum($forum),
                        'label'              => 'topic.label',
                        'translation_domain' => 'CCDNForumForumBundle',
                        'group_by' => 'board.name',
                        'attr' => array(
                            'class' => 'validate[required]'
                        )
                    )
                )
            ;
        }

        $builder
            ->add('body', 'bb_editor',
                array(
                    'label'              => 'post.body-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr'               => array(
                        'class' => 'validate[required,minSize[15]]',
                        'rows' => 8,
                        'acl_group' => 'default'
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
            'intention'           => 'forum_post_update_item',
            'validation_groups'   => array('forum_post_update'),
            'cascade_validation'  => true,
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
