<?php

namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class QuestionType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'];

        //get outil
        $outil = $datas->getChapitre()->getOutil();

        $builder
            ->add('type', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Type',
                'empty_value'   => ' - ',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'TYPE_QUESTION')
                              ->orderBy('ref.order', 'ASC');
                },
                'label_attr'   => array('class' => 'col-md-6 control-label'),
                'attr'         => array('onchange' => 'toggleTypeQuestion()', 'class' => $this->_constraints['type']['class'])
            ))
            ->add('texte', 'text', array(
                'max_length' => $this->_constraints['texte']['maxlength'],
                'required'   => true, 
                'label'      => 'Texte',
                'attr'       => array('class' => $this->_constraints['texte']['class'] )
            ))
            ->add('code', 'text', array(
                'max_length' => $this->_constraints['code']['maxlength'],
                'required'   => true, 
                'label'      => 'Code',
                'label_attr'   => array('class' => 'col-md-4 control-label'),
                'attr'       => array('class' => $this->_constraints['code']['class'] )
            ))
            ->add('infoBulle', 'textarea', array( 
                'required' => false, 
                'label'    => 'Texte de l\'info-bulle'
            ))
            ->add('intro', 'textarea', array( 
                'required' => false, 
                'label'    => 'Introduction'
            ))
            ->add('lien', 'text', array( 
                'required' => false, 
                'label'    => 'Lien',
                'label_attr' => array('class' => 'col-md-2 control-label'),
            ))
            ->add('descriptionLien', 'text', array( 
                'required' => false,
                'label'    => 'Description lien',
                'label_attr' => array('class' => 'col-md-2 control-label'),
            ))
            ->add('ponderation', 'text', array(
                'required'   => true, 
                'label'      => 'Pondération de la question',
                'label_attr' => array('class' => 'col-md-8 control-label'),
                'attr'       => array('class' => $this->_constraints['ponderation']['class'] )
            ))
            ->add('ordreResultat', 'text', array(
                'required'   => false, 
                'label'      => 'Ordre pour les résultats',
                'label_attr' => array('class' => 'col-md-8 control-label'),
                'attr'       => array('class' => $this->_constraints['ordreResultat']['class'] )
            ))
            ->add('categorie', 'entity', array(
                'class'         => 'HopitalNumeriqueAutodiagBundle:Categorie',
                'property'      => 'title',
                'required'      => true,
                'label'         => 'Catégorie',
                'empty_value'   => ' - ',
                'query_builder' => function(EntityRepository $er) use ($outil){
                    return $er->createQueryBuilder('cat')
                              ->where('cat.outil = :outil')
                              ->setParameter('outil', $outil);
                },
                'attr' => array('class' => $this->_constraints['categorie']['class'] )
            ))
            ->add('options', 'textarea', array( 
                'required'   => true, 
                'label'      => 'Items de choix de réponse',
                'attr'       => array('rows' => 3)
            ))
            ->add('noteMinimale', 'text', array(
                'required'   => false, 
                'label'      => 'Note minimale de déclenchement',
                'label_attr' => array('class' => 'col-md-8 control-label'),
                'attr'       => array('class' => $this->_constraints['noteMinimale']['class'] )
            ))
            ->add('seuil', 'text', array(
                'required'   => false, 
                'label'      => 'Seuil de déclenchement',
                'label_attr' => array('class' => 'col-md-8 control-label'),
                'attr'       => array('class' => $this->_constraints['seuil']['class'] )
            ))
            ->add('synthese', 'textarea', array( 
                'required' => false, 
                'label'    => 'Phrase de synthese'
            ))
            ->add('colored', 'checkbox', array(
                'required'   => false,
                'label'      => 'Colorer la réponse'
            ))
            ->add('chapitre', 'hidden', array(
                'mapped' => false,
                'data'   => $datas->getChapitre()->getId()
            ))
        ;
    
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Question'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_autodiag_question';
    }
}
