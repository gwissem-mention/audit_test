<?php

namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class QuestionType extends AbstractType
{
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
                'attr'         => array('onchange' => 'toggleTypeQuestion()')
            ))
            ->add('texte', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Texte'
            ))
            ->add('infoBulle', 'textarea', array( 
                'required' => false, 
                'label'    => 'Texte de l\'info-bulle'
            ))
            ->add('ponderation', 'integer', array(
                'required' => true, 
                'label'    => 'Pondération de la question',
                'label_attr' => array('class' => 'col-md-8 control-label')
            ))
            ->add('ordreResultat', 'integer', array(
                'required' => true, 
                'label'    => 'Ordre pour les résultats',
                'label_attr' => array('class' => 'col-md-8 control-label')
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
                }
            ))
            ->add('options', 'textarea', array( 
                'required'   => true, 
                'label'      => 'Items de choix de réponse',
                'attr'       => array('rows' => 3)
            ))
            ->add('noteMinimale', 'integer', array(
                'required'   => false, 
                'label'      => 'Note minimale de déclenchement',
                'label_attr' => array('class' => 'col-md-8 control-label')
            ))
            ->add('seuil', 'integer', array(
                'required' => false, 
                'label'    => 'Seuil de déclenchement',
                'label_attr' => array('class' => 'col-md-8 control-label')
            ))
            ->add('synthese', 'textarea', array( 
                'required' => false, 
                'label'    => 'Phrase de synthese'
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
