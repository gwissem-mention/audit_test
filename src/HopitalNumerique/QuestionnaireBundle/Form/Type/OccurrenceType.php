<?php
namespace HopitalNumerique\QuestionnaireBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OccurrenceType extends AbstractType
{
    /**
     * @{inherit}               
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', 'text', array(
                'label' => 'Titre de l\'occurrence',
                'required' => true,
                'attr' => array
                (
                    'class' => 'validate[required]'
                )
            ))
        ;
    }
    
    /**
     * @{inherit}            
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\QuestionnaireBundle\Entity\Occurrence'
        ));
    }

    /**
     * @{inherit}            
     */
    public function getName()
    {
        return 'hopitalnumerique_questionnaire_occurrence';
    }
}
