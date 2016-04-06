<?php
namespace HopitalNumerique\ReferenceBundle\Form\Type;

use HopitalNumerique\ReferenceBundle\Manager\SynonymeManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SynonymeType.
 */
class SynonymeType extends AbstractType
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\SynonymeManager SynonymeManager
     */
    private $synonymeManager;


    /**
     * {@inheritdoc}
     */
    public function __construct(SynonymeManager $synonymeManager)
    {
        $this->synonymeManager = $synonymeManager;
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', 'text', [
                'label' => 'Libellé',
                'required' => true,
                'read_only' => (null !== $builder->getData()),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]'
                ]
            ])
        ;
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $this->processExistingSynonyme($event);
        });
    }

    /**
     * Si un synonyme identique existe déjà à la création, il est utilisé (pour éviter les doublons).
     *
     * @param \Symfony\Component\Form\FormEvent $event Event
     */
    private function processExistingSynonyme(FormEvent &$event)
    {
        if (null === $event->getData() || null === $event->getData()->getId()) {
            $libelle = $event->getForm()->get('libelle')->getData();

            $synonyme = $this->synonymeManager->findOneBy(['libelle' => $libelle]);
            if (null !== $synonyme) {
                $event->setData($synonyme);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'HopitalNumerique\\ReferenceBundle\\Entity\\Reference\\Synonyme'
            ])
        ;
    }
}
