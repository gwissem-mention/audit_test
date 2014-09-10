<?php
/**
 * Formulaire d'un processus d'outil.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use HopitalNumerique\AutodiagBundle\Manager\ChapitreManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\AutodiagBundle\Manager\ProcessManager;

/**
 * Formulaire d'un processus d'outil.
 */
class ProcessType extends AbstractType
{
    /**
     * @var array Pour la validation du formulaire
     */
    protected $_constraints = array();
    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\ProcessManager Le manager de Process
     */
    protected $processManager;
    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\ChapitreManager Le manager de Chapitre
     */
    protected $chapitreManager;

    /**
     * Constructeur du formulaire d'un processus d'outil.
     * 
     * @param \HopitalNumerique\AutodiagBundle\Manager\ProcessManager $processManager Manager de Process
     * @param \Symfony\Component\Validator\Validator\LegacyValidator $validator LegacyValidator
     * @param \HopitalNumerique\AutodiagBundle\Manager\ChapitreManager $chapitreManager Manager de Chapitre
     * @return void
     */
    public function __construct(ProcessManager $processManager, $validator, ChapitreManager $chapitreManager)
    {
        $this->_constraints = $processManager->getConstraints($validator);
        $this->processManager = $processManager;
        $this->chapitreManager = $chapitreManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order', 'hidden', array(
                'required' => true
            ))
            ->add('libelle', 'text', array(
                'label' => 'Libellé du processus',
                'required' => true,
                'attr' => array('class' => $this->_constraints['libelle']['class'])
            ))
            ->add('chapitres', 'entity', array(
                'choices'  => $this->chapitreManager->findBy(array('parent' => null)),
                'class'    => 'HopitalNumerique\AutodiagBundle\Entity\Chapitre',
                'property' => 'title',
                'multiple' => true,
                'label'    => 'Chapitres concernés',
                'required' => true
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Process'
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_autodiag_process';
    }
}
