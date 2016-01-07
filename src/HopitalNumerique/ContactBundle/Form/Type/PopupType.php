<?php
namespace HopitalNumerique\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Formulaire de contact de la popup.
 */
class PopupType extends AbstractType
{
    /**
     * @var \Swift_Mailer Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    private $user;


    /**
     * Constructeur.
     */
    public function __construct(SecurityContextInterface $securityContext, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;

        $this->user = (null !== $securityContext->getToken() ? ($securityContext->getToken()->getUser() instanceof User ? $securityContext->getToken()->getUser() : null) : null);
    }


    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var array<string, string>
         */
        $destinataires = ( isset($options['destinataires']) ? $options['destinataires'] : array() );
        /**
         * @var string
         */
        $urlRedirection = ( isset($options['urlRedirection']) ? $options['urlRedirection'] : array() );

        $builder
            ->add('destinataires', 'hidden', array(
                'data' => \json_encode($destinataires)
            ))
            ->add('objet', 'text', array(
                'attr' => array(
                    'class' => 'validate[required]',
                    'maxlength' => 100
                )
            ))
            ->add('message', 'textarea', array(
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 6
                )
            ))
            ->add('urlRedirection', 'hidden', array(
                'data' => $urlRedirection
            ))
        ;
        
        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event)
        {
            $this->postSubmit($event);
        });
    }

    /**
     * Événement postSubmit.
     *
     * @param \Symfony\Component\Form\FormEvent $event FormEvent
     */
    private function postSubmit(FormEvent $event)
    {
        $this->sendCourriel($event->getForm());
    }

    /**
     * Envoie le courriel de la popup.
     *
     * @param \Symfony\Component\Form\FormEvent $event FormEvent
     */
    private function sendCourriel(FormInterface $form)
    {
        $swiftMessage = \Swift_Message::newInstance();
        $bodyHtml = $this->twig->loadTemplate('NodevoMailBundle::template.mail.html.twig')->render(array('content' => nl2br($form->get('message')->getData())));
        $bodyTxt = $this->twig->loadTemplate('NodevoMailBundle::template.mail.txt.twig')->render(array('content' => $form->get('message')->getData()));

        $swiftMessage
            ->setSubject($form->get('objet')->getData())
            ->setBody($bodyTxt)
            ->addPart($bodyHtml, 'text/html')
        ;

        if (null !== $this->user) {
            $swiftMessage->addFrom($this->user->getEmail(), $this->user->getAppellation());
        }

        foreach (json_decode($form->get('destinataires')->getData()) as $destinataireAdresseElectronique => $destinataireNom) {
            $swiftMessage->addTo($destinataireAdresseElectronique, $destinataireNom);
        }

        $swiftMessage->setSender($this->user->getEmail(), $this->user->getUsername());

        $this->mailer->send($swiftMessage);
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setOptional(array('destinataires', 'urlRedirection'))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_contactbundle_popup';
    }
}
