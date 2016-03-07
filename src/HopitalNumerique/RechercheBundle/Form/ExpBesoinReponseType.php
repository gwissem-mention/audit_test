<?php
namespace HopitalNumerique\RechercheBundle\Form;

use HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Formulaire d'une réponse d'une expression de besoin.
 */
class ExpBesoinReponseType extends AbstractType
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
            ->setAction($this->router->generate('hopitalnumerique_expbesoin_reponse_edit_ajax', ['id' => $builder->getData()->getId()]))
            ->add('image', 'hidden', [
                'required' => false
            ])
            ->add('imageFile', 'file', array(
                'label' => 'Image',
                'required' => false
            ))
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $this->verifyImage($event->getForm(), $event->getData());
        });
    }

    /**
     * Vérifie la validité de l'image.
     *
     * @param \Symfony\Component\Form\FormInterface              $form      Formulaire
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence
     */
    private function verifyImage(FormInterface $form, ExpBesoinReponses $expBesoinReponse)
    {
        if (null !== $expBesoinReponse->getImageFile() && !$expBesoinReponse->imageFileIsValid()) {
            $form->get('imageFile')->addError(new FormError('Veuillez choisir une image inférieure à '.intval(Systeme::getFileUploadMaxSize() / 1024 / 1024).' Mo.'));
        }
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class' => 'HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses'
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_recherche_expbesoinreponse';
    }
}
