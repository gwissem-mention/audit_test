<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Formulaire d'édition d'un commentaire de la communauté de pratique.
 */
class CommentaireType extends \Symfony\Component\Form\AbstractType
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface Router
     */
    private $router;

    /**
     * Constructeur.
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->router->generate($options['redirectionRoute'], $options['redirectionRouteParams']));

        $builder
            ->add('message', 'textarea', [
                'required' => true,
                'attr' => [
                    'class' => 'validate[required] tinyMceCode',
                    'rows' => 4,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire',
                'redirectionRouteParams' => [],
            ])
            ->setRequired(['redirectionRoute'])
            ->setOptional(['redirectionRouteParams'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_commentaire';
    }
}
