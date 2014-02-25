<?php

namespace HopitalNumerique\EtablissementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class EtablissementType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
                'max_length' => $this->_constraints['nom']['maxlength'],
                'required'   => true, 
                'label'      => 'Nom',
                'attr'       => array('class' => $this->_constraints['nom']['class'] )
            ))
            ->add('finess', 'text', array(
                'max_length' => $this->_constraints['finess']['maxlength'],
                'required'   => true, 
                'label'      => 'FINESS Geographique',
                'attr'       => array('class' => $this->_constraints['finess']['class'] )
            ))
            ->add('typeOrganisme', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Type Organisme',
                'empty_value'   => ' - ',
                'attr'        => array('class' => $this->_constraints['typeOrganisme']['class'] ),                
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'TYPE_ORGANISME')
                              ->orderBy('ref.order', 'ASC');
                }
            ))
            ->add('region', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Région',
                'empty_value'   => ' - ',
                'attr'          => array('class' => $this->_constraints['region']['class'] ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'REGION')
                              ->orderBy('ref.order', 'ASC');
                }
            ))
            ->add('departement', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Département',
                'empty_value'   => ' Sélectionnez une région avant le département ',
                'attr'          => array('class' => $this->_constraints['departement']['class'] ),
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'DEPARTEMENT')
                        ->orderBy('ref.libelle', 'ASC');                        
                }
            ))
            ->add('adresse', 'textarea', array(
                'max_length' => $this->_constraints['adresse']['maxlength'],
                'required'   => true, 
                'label'      => 'Adresse',
                'attr'       => array('class' => $this->_constraints['adresse']['class'] )
            ))
            ->add('ville', 'text', array(
                'max_length' => $this->_constraints['ville']['maxlength'],
                'required'   => true, 
                'label'      => 'Ville',
                'attr'       => array('class' => $this->_constraints['ville']['class'] )
            ))
            ->add('codepostal', 'text', array(
                'max_length' => $this->_constraints['codepostal']['maxlength'],
                'required'   => true, 
                'label'      => 'Code Postal',
                'attr'       => array('class' => $this->_constraints['codepostal']['class'] )
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_etablissement_etablissement';
    }
}
