<?php

namespace Nodevo\MailBundle\Form\Type;

use Nodevo\MailBundle\Entity\Mail;
use Symfony\Component\Form\AbstractType;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class RecommandationType
 */
class RecommandationType extends AbstractType
{
    /**
     * @var RouterInterface Router
     */
    private $router;

    /**
     * RecommandationType constructor.
     *
     * @param RouterInterface $router
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
         * @var Mail
         */
        $recommandationMail = $options['mail'];

        /**
         * @var User|null
         */
        $expediteur = $options['expediteur'];

        /**
         * @var string
         */
        $url = $options['url'];

        if ($builder->getAction() == null) {
            $builder->setAction($this->router->generate('nodevo_mail_recommandation_popin'));
        }

        $builder
            ->add('destinataire', EmailType::class, [
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required,custom[email]]',
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
            ->add('expediteur', EmailType::class, [
                'data' => (null !== $expediteur ? $expediteur->getEmail() : ''),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required,custom[email]]',
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
            ->add('objet', TextType::class, [
                'data' => $recommandationMail->getObjet(),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]',
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
            ->add('message', TextareaType::class, [
                'data' => str_replace(
                    '%url',
                    $url,
                    $recommandationMail->getBody()
                ),
                'attr' => [
                    'rows' => 10,
                    'class' => 'validate[required]',
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
            ->add('url', HiddenType::class, [
                'data' => $url,
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
            ->setAllowedTypes(['mail' => '\Nodevo\MailBundle\Entity\Mail'])
        ;
    }
}
