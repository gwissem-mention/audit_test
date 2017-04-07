<?php

namespace HopitalNumerique\PublicationBundle\Form\Type\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Media;
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
        $view->vars['public_path'] = $form->getData()->getPath();
        $view->vars['placeholder'] = pathinfo($form->getData()->getPath())['filename'];
        $view->vars['nodeName'] = $form->getData()->getNode() ? $form->getData()->getNode()->getTitle() : null;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'label_format' => 'form.document.media.%name%',
        ]);
    }
}
