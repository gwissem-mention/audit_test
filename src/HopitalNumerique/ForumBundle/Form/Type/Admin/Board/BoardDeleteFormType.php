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
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @category CCDNForum
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 */
class BoardDeleteFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $boardClass;

    /**
     * @param string $boardClass
     */
    public function __construct($boardClass)
    {
        $this->boardClass = $boardClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $trueValidator = function (FormEvent $event) {
            $form = $event->getForm();

            $confirm = $form->get('confirm_delete')->getData();

            if (empty($confirm) || $confirm == false) {
                $form['confirm_delete']->addError(new FormError('Vous devez cocher ces cases.'));
            }
        };

        $builder
            ->add('confirm_delete', 'checkbox',
                [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'board.confirm-delete-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr' => [
                        'class' => 'validate[required]',
                    ],
                    'constraints' => [
                        new True(),
                        new NotBlank(),
                    ],
                ]
            )
            ->add('confirm_subordinates', 'checkbox',
                [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'board.confirm-delete-subordinates-label',
                    'translation_domain' => 'CCDNForumForumBundle',
                    'attr' => [
                        'class' => 'validate[required]',
                    ],
                    'constraints' => [
                        new True(),
                        new NotBlank(),
                    ],
                ]
            )
            ->addEventListener(FormEvents::POST_BIND, $trueValidator)
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
            'intention' => 'forum_board_delete_item',
            'validation_groups' => ['forum_board_delete'],
            'cascade_validation' => true,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Forum_BoardDelete';
    }
}
