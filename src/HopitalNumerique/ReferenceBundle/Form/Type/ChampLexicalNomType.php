<?php

namespace HopitalNumerique\ReferenceBundle\Form\Type;

use HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom;
use HopitalNumerique\ReferenceBundle\Manager\ChampLexicalNomManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ChampLexicalNomType.
 */
class ChampLexicalNomType extends AbstractType
{
    /**
     * @var ChampLexicalNomManager ChampLexicalNomManager
     */
    private $champLexicalNomManager;

    /**
     * {@inheritdoc}
     */
    public function __construct(ChampLexicalNomManager $champLexicalNomManager)
    {
        $this->champLexicalNomManager = $champLexicalNomManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'read_only' => (null !== $builder->getData()),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]',
                ],
            ])
        ;
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $this->processExistingChampLexicalNom($event);
        });
    }

    /**
     * Si un terme du champ lexical identique existe déjà à la création, il est utilisé (pour éviter les doublons).
     *
     * @param FormEvent $event Event
     */
    private function processExistingChampLexicalNom(FormEvent &$event)
    {
        if (null === $event->getData() || null === $event->getData()->getId()) {
            $libelle = $event->getForm()->get('libelle')->getData();

            $champLexicalNom = $this->champLexicalNomManager->findOneBy(['libelle' => $libelle]);
            if (null !== $champLexicalNom) {
                $event->setData($champLexicalNom);
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
                'data_class' => ChampLexicalNom::class,
            ])
        ;
    }
}
