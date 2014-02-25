<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ContractualisationType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation
     * 
     * @param  FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param  array                $options Data passée au formulaire
     * 
     * @return void                 
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file', array(
                'required' => true, 
                'label'    => 'Fichier objet'
            ))
            ->add('path', 'hidden')
            ->add('nomDocument', 'text', array(
                'max_length' => $this->_constraints['nomDocument']['maxlength'], 
                'required'   => true, 
                'label'      => 'Nom du document',
                'attr'       => array('class' => $this->_constraints['nomDocument']['class'] )
            ))
            ->add('typeDocument', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => true,
                    'label'       => 'Type de document',
                    'empty_value' => ' - ',
                    'attr'        => array('class' => $this->_constraints['typeDocument']['class'] ),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'DOCUMENT_CONTRACTUALISATION_TYPE')
                        ->orderBy('ref.order', 'ASC');
                    }
            ))
            ->add('dateRenouvellement', 'genemu_jquerydate', array(
                'required' => false, 
                'label'    => 'Date de renouvellement',
                'widget'   => 'single_text'
            )) 
            ->add('archiver', 'checkbox', array(
                'required' => false, 
                'label'    => 'Archiver le document ?',
                'attr'     => array()//array('class' => $this->_constraints['archiver']['class'] )
            ))       ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\UserBundle\Entity\Contractualisation'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_user_contractualisation';
    }
}
