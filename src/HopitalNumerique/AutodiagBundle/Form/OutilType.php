<?php

namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class OutilType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $outil = $options['data'];

        $builder
            ->add('title', 'text', array(
                'max_length' => $this->_constraints['title']['maxlength'],
                'required'   => true, 
                'label'      => 'Titre',
                'attr'       => array('class' => $this->_constraints['title']['class'] )
            ))
            ->add('alias', 'text', array(
                'max_length' => $this->_constraints['alias']['maxlength'],
                'required'   => false, 
                'label'      => 'Alias',
                'attr'       => array('class' => $this->_constraints['alias']['class'] )
            ))
            ->add('columnChart', 'checkbox', array(
                'label'    => 'Afficher la restitution en graphique barres ?',
                'required' => false,
                'attr'     => array('onclick' => "toggle('columnChart');")
            ))
            ->add('columnChartLabel', 'text', array(
                'max_length' => 255, 
                'required'   => false, 
                'label'      => 'Libellé du résultat sur le graphique barre',
                'attr'       => array('class' => $outil->isColumnChart() ? 'validate[required,maxSize[255]]' : '' )
            ))
            ->add('columnChartAxe', 'choice', array(
                'choices'     => array(1 => 'Chapitres', 2 => 'Catégories'),
                'required'    => false, 
                'empty_value' => ' - ',
                'label'       => 'Axes du graphique barre',
                'attr'       => array('class' => $outil->isColumnChart() ? 'validate[required]' : '' )
            ))
            ->add('radarChart', 'checkbox', array(
                'label'    => 'Afficher la restitution en graphique radar ?',
                'required' => false,
                'attr'     => array('onclick' => "toggle('radarChart');")
            ))
            ->add('radarChartLabel', 'text', array(
                'max_length' => 255, 
                'required'   => false, 
                'label'      => 'Libellé du résultat sur le graphique radar',
                'attr'       => array('class' => $outil->isRadarChart() ? 'validate[required,maxSize[255]]' : '' )
            ))
            ->add('radarChartAxe', 'choice', array(
                'choices'     => array(1 => 'Chapitres', 2 => 'Catégories'),
                'required'    => false, 
                'empty_value' => ' - ',
                'label'       => 'Axes du graphique radar',
                'attr'       => array('class' => $outil->isRadarChart() ? 'validate[required]' : '' )
            ))
            ->add('tableChart', 'checkbox', array(
                'label'    => 'Afficher la restitution sous forme de table ?',
                'required' => false
            ))
            ->add('statut', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Statut',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'ETAT')
                              ->orderBy('ref.order', 'ASC');
                }
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Outil'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_autodiag_outil';
    }
}