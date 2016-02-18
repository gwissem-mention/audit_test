<?php
namespace HopitalNumerique\ExpertBundle\Form\ActiviteExpert;

use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;
use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert\Paiement;
use HopitalNumerique\ExpertBundle\Form\ActiviteExpert\PaiementType;
use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form PaiementsType.
 */
class PaiementsType extends AbstractType
{
    /**
     * @var \Nodevo\MailBundle\Manager\MailManager MailManager
     */
    private $mailManager;


    /**
     * Constructeur.
     */
    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paiements', 'collection', [
                'type' => PaiementType::class
            ])
            ->add('adresseElectronique', 'email', [
                'mapped' => false,
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required,custom[email]]'
                )
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $this->initPaiements($event);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->processMailing($event);
            });
        ;
    }

    /**
     * Initialise les sous-formulaires des paiements.
     *
     * @param \Symfony\Component\Form\FormEvent $event FormEvent
     */
    private function initPaiements(FormEvent $event)
    {
        /**
         * @var \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert
         */
        $activiteExpert = $event->getData();

        // Supprime les paiements en trop (dans le cas où un expert aurait été enlevé, on ne l'affiche plus)
        foreach ($activiteExpert->getPaiements() as $paiement) {
            if (!$activiteExpert->hasExpertConcerne($paiement->getExpert())) {
                $activiteExpert->removePaiementForExpert($paiement->getExpert());
            }
        }

        // Ajoute les paiements manquants (dans le cas où un expert a été ajouté ou si les paiements n'ont pas encore été enregistrés)
        foreach ($activiteExpert->getExpertConcernes() as $expertConcerne) {
            if (!$activiteExpert->hasPaiementForExpert($expertConcerne)) {
                $paiement = new Paiement();
                $paiement->setActiviteExpert($activiteExpert);
                $paiement->setExpert($expertConcerne);
                $activiteExpert->addPaiement($paiement);
            }
        }
    }

    /**
     * Gère l'envoi de courriel à la soumission.
     *
     * @param \Symfony\Component\Form\FormEvent $event FormEvent
     */
    private function processMailing(FormEvent $event)
    {
        if ($event->getForm()->isValid()) {
            $this->mailManager->sendExpertActivitePaimentMail($event->getData(), $event->getForm()->get('adresseElectronique')->getData());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ExpertBundle\Entity\ActiviteExpert'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_expert_activiteexpert_paiements';
    }
}
