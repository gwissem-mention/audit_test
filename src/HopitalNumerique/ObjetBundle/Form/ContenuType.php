<?php
namespace HopitalNumerique\ObjetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContenuType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text', array(
                'max_length' => $this->_constraints['titre']['maxlength'],
                'required'   => true, 
                'label'      => 'Titre',
                'attr'       => array('class' => $this->_constraints['titre']['class'] )
            ))
            ->add('alias', 'text', array(
                'max_length' => $this->_constraints['alias']['maxlength'],
                'required'   => false, 
                'label'      => 'Alias',
                'attr'       => array('class' => $this->_constraints['alias']['class'] )
            ))
            ->add('contenu', 'textarea', array(
                'required' => true, 
                'label'    => 'Contenu',
                'attr'     => array('class' => $this->_constraints['contenu']['class'] )
            ))
            ->add('modified', 'hidden', array(
                'mapped' => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ObjetBundle\Entity\Contenu'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_objet_contenu';
    }
}