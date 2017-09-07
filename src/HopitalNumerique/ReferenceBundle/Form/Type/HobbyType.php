<?php

namespace HopitalNumerique\ReferenceBundle\Form\Type;

use HopitalNumerique\ReferenceBundle\Entity\Reference\Hobby;
use HopitalNumerique\ReferenceBundle\Repository\HobbyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HobbyType
 */
class HobbyType extends AbstractType
{
    private $hobbyRepository;

    /**
     * HobbyType constructor.
     *
     * @param HobbyRepository $hobbyRepository
     */
    public function __construct(HobbyRepository $hobbyRepository)
    {
        $this->hobbyRepository = $hobbyRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'read_only' => (null !== $builder->getData()),
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
        ;
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $this->processExistingHobby($event);
        });
    }

    /**
     * @param FormEvent $event
     */
    private function processExistingHobby(FormEvent &$event)
    {
        if (null === $event->getData() || null === $event->getData()->getId()) {
            $label = $event->getForm()->get('label')->getData();

            $hobby = $this->hobbyRepository->findOneBy(['label' => $label]);

            if (null !== $hobby) {
                $event->setData($hobby);
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Hobby::class,
            ])
        ;
    }
}
