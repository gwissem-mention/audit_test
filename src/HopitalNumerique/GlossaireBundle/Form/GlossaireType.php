<?php

namespace HopitalNumerique\GlossaireBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class GlossaireType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mot', 'text', array(
                'max_length' => $this->_constraints['mot']['maxlength'],
                'required'   => true, 
                'label'      => 'Mot',
                'attr'       => array('class' => $this->_constraints['mot']['class'] )
            ))
            ->add('intitule', 'textarea', array(
                'required'   => false, 
                'label'      => 'Intitulé'
            ))
            ->add('description', 'textarea', array(
                'required'   => false, 
                'label'      => 'Description complète',
                'attr'     => array('class' => 'tinyMce')
            ))
            ->add('etat', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Etat',
                'attr'          => array('class' => $this->_constraints['etat']['class'] ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'ETAT')
                              ->orderBy('ref.order', 'ASC');
                }
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\GlossaireBundle\Entity\Glossaire'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_glossaire_glossaire';
    }
}
