<?php

namespace HopitalNumerique\PublicationBundle\Form\Type\Converter;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document;
use HopitalNumerique\PublicationBundle\Form\Type\Converter\Document\MediaType;
use HopitalNumerique\PublicationBundle\Form\Type\Converter\Document\NodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Document\Node $tree */
        $tree = $builder->getData()->getTree();
        $builder
            ->add('childrens', CollectionType::class, [
                'entry_type' => NodeType::class,
                'data' => null !== $tree ? $tree->getChildrens() : [],
                'mapped' => false,
            ])
            ->add('medias', CollectionType::class, [
                'entry_type' => MediaType::class,
                'data' => null !== $tree ? $tree->getMedias(true) : [],
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
