<?php

namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ChapitreType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'];

        $builder
            ->add('title', 'text', array(
                'max_length' => $this->_constraints['title']['maxlength'],
                'required'   => true, 
                'label'      => 'Titre',
                'label_attr' => array('class' => 'col-md-1 control-label'),
                'attr'       => array('class' => $this->_constraints['title']['class'] )
            ))
            ->add('alias', 'text', array(
                'max_length' => $this->_constraints['alias']['maxlength'],
                'required'   => false, 
                'label'      => 'Alias',
                'label_attr' => array('class' => 'col-md-2 control-label'),
                'attr'       => array('class' => $this->_constraints['alias']['class'] )
            ))
            ->add('noteOptimale', 'text', array(
                'required'   => false, 
                'label'      => 'Note optimale',
                'label_attr' => array('class' => 'col-md-9 control-label'),
                'attr'       => array('class' => $this->_constraints['noteOptimale']['class'] )
            ))
            ->add('noteMinimale', 'text', array(
                'required'   => false, 
                'label'      => 'Note minimale de déclenchement',
                'label_attr' => array('class' => 'col-md-9 control-label'),
                'attr'       => array('class' => $this->_constraints['noteMinimale']['class'] )
            ))
            ->add('synthese', 'textarea', array(
                'required'   => false, 
                'label'      => 'Phrase de synthèse',
                'label_attr' => array('class' => 'col-md-12'),
                'attr'       => array('rows' => 10)
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Chapitre'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_autodiag_chapitre';
    }
}
