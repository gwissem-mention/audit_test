<?php
namespace Nodevo\MailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Formulaire de recommandation à un ami.
 */
class RecommandationType extends AbstractType
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
        /**
         * @var \Nodevo\MailBundle\Entity\Mail
         */
        $recommandationMail = $options['mail'];
        /**
         * @var \HopitalNumerique\UserBundle\Entity\User|null
         */
        $expediteur = $options['expediteur'];
        /**
         * @var string
         */
        $url = $options['url'];

        $builder
            ->setAction($this->router->generate('nodevo_mail_recommandation_popin'))
            ->add('destinataire', 'email', [
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required,custom[email]]',
                    'data-prompt-position' => 'bottomLeft'
                ]
            ])
            ->add('expediteur', 'email', [
                'data' => (null !== $expediteur ? $expediteur->getEmail() : ''),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required,custom[email]]',
                    'data-prompt-position' => 'bottomLeft'
                ]
            ])
            ->add('objet', 'text', [
                'data' => $recommandationMail->getObjet(),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]',
                    'data-prompt-position' => 'bottomLeft'
                ]
            ])
            ->add('message', 'textarea', [
                'data' => str_replace(
                    '%url',
                    $url,
                    $recommandationMail->getBody()
                ),
                'attr' => [
                    'rows' => 10,
                    'class' => 'validate[required]',
                    'data-prompt-position' => 'bottomLeft'
                ]
            ])
            ->add('url', 'hidden', [
                'data' => $url
            ])
        ;
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setRequired(['mail', 'expediteur', 'url'])
            ->setAllowedTypes([
                'mail' => '\Nodevo\MailBundle\Entity\Mail'
            ])
        ;
    }
}
