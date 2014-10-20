<?php

namespace HopitalNumerique\QuestionnaireBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QuestionnaireGestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
                'required'   => true,
                'label'      => 'Titre du questionnaire',
                'attr'       => array(
                    'class'     => 'validate[required]'
                )
            ));

        $builder
            ->add('lien', 'text', array(
                'required'   => false,
                'label'      => 'Lien de redirection'
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_questionnaire_gestion_questionnaire';
    }
}
