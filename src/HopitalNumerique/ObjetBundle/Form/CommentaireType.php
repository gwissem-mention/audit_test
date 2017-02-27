<?php

namespace HopitalNumerique\ObjetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('texte', 'textarea', [
                'required' => true,
                'label' => 'Texte du commentaire',
                'attr' => [
                    'rows' => 8,
                ],
            ])
            ->add('publier', 'checkbox', [
                'label' => 'Publier ?',
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ObjetBundle\Entity\Commentaire',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_objet_commentaire';
    }
}
