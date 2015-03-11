<?php

namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Manager\ProcessManager;
use HopitalNumerique\AutodiagBundle\Manager\ChapitreManager;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;

class OutilType extends AbstractType
{
    private $_constraints = array();
    private $validator;
    private $processManager;
    private $chapitreManager;
    private $questionnaireManager;

    public function __construct($manager, $validator, ProcessManager $processManager, ChapitreManager $chapitreManager, QuestionnaireManager $questionnaireManager)
    {
        $this->validator = $validator;
        $this->_constraints = $manager->getConstraints( $validator );
        
        $this->processManager = $processManager;
        $this->chapitreManager = $chapitreManager;
        $this->questionnaireManager = $questionnaireManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $outil = $options['data'];
        $processFormulaire = new ProcessType($this->processManager, $this->validator, $this->chapitreManager);
        $processFormulaire->setOutil($outil);

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
            ->add('questionnairePrealable', 'entity', array(
                'label'    => 'Questionnaire préalable',
                'required' => false,
                'class'         => 'HopitalNumeriqueQuestionnaireBundle:Questionnaire',
                'property'      => 'nom',
                'choices' => $this->questionnaireManager->findBy(array('lock' => false), array('nom' => 'ASC')),
                'empty_value' => ' - Aucun questionnaire - '
            ))
            ->add('columnChart', 'checkbox', array(
                'label'    => 'Afficher la restitution en graphique barres ?',
                'required' => false,
                'attr'     => array('onclick' => "toggle('columnChart');")
            ))
            ->add('columnChartLabel', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Libellé du résultat sur le graphique barre',
                'attr'       => array('class' => $outil->isColumnChart() ? 'validate[required,maxSize[255]]' : '' )
            ))
            ->add('columnChartAxe', 'choice', array(
                'choices'     => array(1 => 'Chapitres', 2 => 'Catégories'),
                'required'    => true, 
                'empty_value' => ' - ',
                'label'       => 'Axes du graphique barre',
                'attr'        => array('class' => $outil->isColumnChart() ? 'validate[required]' : '' )
            ))
            ->add('processChart', 'checkbox', array(
                'label'    => 'Afficher la restitution par processus ?',
                'required' => false,
                'attr'     => array('onclick' => "toggle('processChart');")
            ))
            ->add('processChartLabel', 'text', array(
                'max_length' => 255, 
                'required'   => true,
                'label'      => 'Libellé du résultat par processus',
                'attr'       => array('class' => $outil->isProcessChart() ? 'validate[required,maxSize[255]]' : '' )
            ))
            ->add('process', 'collection', array(
                'type' => $processFormulaire,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('radarChart', 'checkbox', array(
                'label'    => 'Afficher la restitution en graphique radar ?',
                'required' => false,
                'attr'     => array('onclick' => "toggle('radarChart');")
            ))
            ->add('radarChartLabel', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Libellé du résultat sur le graphique radar',
                'attr'       => array('class' => $outil->isRadarChart() ? 'validate[required,maxSize[255]]' : '' )
            ))
            ->add('radarChartAxe', 'choice', array(
                'choices'     => array(1 => 'Chapitres', 2 => 'Catégories'),
                'required'    => true, 
                'empty_value' => ' - ',
                'label'       => 'Axes du graphique radar',
                'attr'        => array('class' => $outil->isRadarChart() ? 'validate[required]' : '' )
            ))
            ->add('radarChartAfficheBenchmark', 'checkbox', array(
                'required'    => false,
                'label'       => 'Afficher le benchmark ?',
                'attr'        => array('class' => '', 'onclick' => "$('.radarChart_afficheBenchmark').slideToggle();")
            ))
            ->add('radarChartBenchmarkAfficheDecile2', 'checkbox', array(
                'required'    => false,
                'label'       => 'Afficher le deuxième décile ?',
                'attr'        => array('class' => '', 'onclick' => "$('.radarChart_benchmarkDecile2').slideToggle();")
            ))
            ->add('radarChartBenchmarkCouleurDecile2', 'choice', array(
                'required'    => false,
                'label'       => 'Couleur du deuxième décile ?',
                'choices'     => array('vert' => 'Vert', 'rouge' => 'Rouge'),
                'empty_value' => false,
                'attr'        => array('class' => '')
            ))
            ->add('radarChartBenchmarkAfficheMoyenne', 'checkbox', array(
                'required'    => false,
                'label'       => 'Afficher la moyenne ?',
                'attr'        => array('class' => '')
            ))
            ->add('radarChartBenchmarkAfficheDecile8', 'checkbox', array(
                'required'    => false,
                'label'       => 'Afficher le huitième décile ?',
                'attr'        => array('class' => '', 'onclick' => "$('.radarChart_benchmarkDecile8').slideToggle();")
            ))
            ->add('radarChartBenchmarkCouleurDecile8', 'choice', array(
                'required'    => false,
                'label'       => 'Couleur du huitième décile ?',
                'choices'     => array('vert' => 'Vert', 'rouge' => 'Rouge'),
                'empty_value' => false,
                'attr'        => array('class' => '')
            ))
            ->add('tableChart', 'checkbox', array(
                'label'    => 'Afficher la restitution sous forme de table ?',
                'required' => false,
                'attr'     => array('onclick' => "toggle('tableChart');")
            ))
            ->add('tableChartAfficheTotal', 'checkbox', array(
                'label'    => 'Afficher le total ?',
                'required' => false,
                'attr'       => array('class' => '')
            ))
            ->add('centPourcentReponseObligatoire', 'checkbox', array(
                'label'    => 'Activer l\'affichage des résultats uniquement si toutes les questions sont renseignées',
                'required' => false
            ))
            ->add('masquerAnalyse', 'checkbox', array(
                'label'    => 'Masquer l\'onglet analyse en front',
                'required' => false
            ))
            ->add('masquerReponse', 'checkbox', array(
                'label'    => 'Masquer l\'onglet réponses en front',
                'required' => false
            ))
            ->add('planActionPriorise', 'checkbox', array(
                'label'    => 'Plan d\'action priorisé',
                'required' => false
            ))
            ->add('instruction', 'textarea', array(
                'required'   => false, 
                'label'      => 'Instructions',
                'attr'       => array('rows' => 2, 'class' => 'tinyMce')
            ))
            ->add('commentaireRestitution', 'textarea', array(
                'required'   => false, 
                'label'      => 'Commentaire affiché lors de la restitution',
                'attr'       => array('rows' => 2, 'class' => 'tinyMce')
            ))
            ->add('commentaireGraphBarre', 'textarea', array(
                'required'   => false, 
                'label'      => 'Commentaire affiché lors de la restitution',
                'attr'       => array('rows' => 2, 'class' => 'tinyMce')
            ))
            ->add('commentaireGraphPrecessus', 'textarea', array(
                'required'   => false, 
                'label'      => 'Commentaire affiché lors de la restitution',
                'attr'       => array('rows' => 2, 'class' => 'tinyMce')
            ))
            ->add('commentaireGraphRadar', 'textarea', array(
                'required'   => false, 
                'label'      => 'Commentaire affiché lors de la restitution',
                'attr'       => array('rows' => 2, 'class' => 'tinyMce')
            ))
            ->add('commentaireAnalyseResultat', 'textarea', array(
                'required'   => false, 
                'label'      => 'Commentaire affiché lors de la restitution',
                'attr'       => array('rows' => 2, 'class' => 'tinyMce')
            ))
            ->add('commentaireReponses', 'textarea', array(
                'required'   => false, 
                'label'      => 'Commentaire affiché lors de la restitution',
                'attr'       => array('rows' => 2, 'class' => 'tinyMce')
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
