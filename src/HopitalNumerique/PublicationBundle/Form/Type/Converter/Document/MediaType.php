<?php

namespace HopitalNumerique\PublicationBundle\Form\Type\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Media;
use HopitalNumerique\PublicationBundle\Model\Converter\Document\WalkableNode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('excluded', CheckboxType::class)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Media $media */
        $media = $form->getData();

        $view->vars['public_path'] = $media->getPath();
        $view->vars['placeholder'] = pathinfo($media->getPath())['filename'];
        $view->vars['nodeName'] = $media->getNode() ? $media->getNode()->getTitle() : null;

        $level = null;
        if ($media->getNode()) {
            $node = new WalkableNode($media->getNode());
            $level = $node->getLevel(true);

            if (null !== $view->vars['nodeName']) {
                $view->vars['nodeName'] = sprintf('%s. %s', implode('.', $level), $view->vars['nodeName']);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'label_format' => 'form.document.media.%name%',
        ]);
    }
}
