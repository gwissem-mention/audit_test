<?php

namespace HopitalNumerique\PublicationBundle\Form\Type\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use HopitalNumerique\PublicationBundle\Form\DataTransformer\Converter\Document\SquashWithPreviousTransformer;
use HopitalNumerique\PublicationBundle\Model\Converter\Document\WalkableNode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('excluded', CheckboxType::class, [
                'required' => false,
            ])
            ->add('squashIn', CheckboxType::class, [
                'required' => false,
            ])
            ->add('childrens', CollectionType::class, [
                'entry_type' => NodeType::class,
            ])
        ;

        $squashTransformer = new SquashWithPreviousTransformer();

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($squashTransformer) {
                if (null !== $event->getData()) {
                    $squashTransformer->setNode($event->getData());

                    // Delete squashIn on first node
                    $prev = (new WalkableNode($event->getData()))->prev();
                    if (null === $prev || $prev->getDeep() === 0) {
                        $event->getForm()->remove('squashIn');
                    }
                }
            })
        ;

        $builder->get('squashIn')->addModelTransformer($squashTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Node::class,
            'label_format' => 'form.document.node.%name%',
        ]);
    }
}
