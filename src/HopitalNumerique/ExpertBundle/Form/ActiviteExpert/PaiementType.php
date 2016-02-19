<?php
namespace HopitalNumerique\ExpertBundle\Form\ActiviteExpert;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form PaiementType.
 */
class PaiementType extends AbstractType
{
    /**
     * @var \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert\Paiement
     */
    private $paiementInitial;
    
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vacationsCount', 'number', array(
                'attr' => array(
                    'class' => 'validate[required,custom[integer]]'
                )
            ))
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $this->verifyVacationsCount($event);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->saveVacationsCount($event);
            })
        ;
    }

    /**
     * Vérifie si le nombre de vacations saisi est valide.
     *
     * @param \Symfony\Component\Form\FormEvent $event FormEvent
     */
    private function verifyVacationsCount(FormEvent $event)
    {
        $this->paiementInitial = clone $event->getForm()->getNormData();
    }

    /**
     * Sauvegarde le bon nombre de vacations.
     *
     * @param \Symfony\Component\Form\FormEvent $event FormEvent
     */
    private function saveVacationsCount(FormEvent $event)
    {
        /**
         * @var \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert\Paiement
         */
        $paiementSubmitted = $event->getData();

        if (intval($paiementSubmitted->getVacationsCount()) < 0 || intval($paiementSubmitted->getVacationsCount()) > intval($this->paiementInitial->getVacationsCount())) {
            $event->getForm()->get('vacationsCount')->addError(new FormError('Veuillez choisir un nombre positif, inférieur ou égal à '.$this->paiementInitial->getVacationsCount().'.'));
        }

        if ($event->getForm()->isValid()) {
            // On soustrait aux vacations actuelles les vacations saisies (elles ne sont pas remplacées, on saisit le nombre de vacations que l'on utilise)
            $nombreVacationsRestants = intval($this->paiementInitial->getVacationsCount()) - intval($paiementSubmitted->getVacationsCount());
            $event->getData()->setVacationsCount($nombreVacationsRestants);
        }
    }


    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ExpertBundle\Entity\ActiviteExpert\Paiement'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_expert_activiteexpert_paiement';
    }
}
