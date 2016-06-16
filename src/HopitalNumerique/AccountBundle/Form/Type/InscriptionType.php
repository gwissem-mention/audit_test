<?php
namespace HopitalNumerique\AccountBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class InscriptionType extends AbstractType
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
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $urlRedirection = $options['url_redirection'];

        $builder
            ->setAction($this->router->generate('hopitalnumerique_account_inscription_popin'))
            ->add('urlRedirection', 'hidden', [
                'mapped' => false,
                'data' => $urlRedirection
            ])
            ->add('email', 'email', [
                'label' => 'Email',
                'attr' => [
                    'data-validation-engine' => 'validate[required,custom[email]]'
                ]
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'HopitalNumerique\UserBundle\Entity\User'
            ))
            ->setRequired(['url_redirection'])
        ;
    }
}
