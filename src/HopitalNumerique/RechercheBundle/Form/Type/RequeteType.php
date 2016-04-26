<?php
namespace HopitalNumerique\RechercheBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Formulaire de la requête (en popin).
 */
class RequeteType extends AbstractType
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
        $builder
            ->setAction($this->router->generate('hopitalnumerique_recherche_referencement_requete_save'))
            ->add('nom', null, [
                'attr' => [
                    'maxlength' => 128,
                    'data-validation-engine' => 'validate[required]'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\RechercheBundle\Entity\Requete'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_recherche_requete';
    }
}
