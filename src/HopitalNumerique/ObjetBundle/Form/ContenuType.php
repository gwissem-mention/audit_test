<?php
namespace HopitalNumerique\ObjetBundle\Form;

use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContenuType extends AbstractType
{
    private $_constraints = array();

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager ObjetManager
     */
    private $objetManager;


    public function __construct($manager, $validator, ObjetManager $objetManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->objetManager = $objetManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $domaine = $options['domaine'];
        $objetsOptions = [
            'mapped' => false,
            'choices' => $this->objetManager->getObjetsAndContenuForFormTypeChoices(),
            'label' => 'Productions liées',
            'multiple' => true,
            'attr' => [
                'class' => 'select2'
            ]
        ];
        if (null !== $builder->getData()->getObjets()) {
            $objetsOptions['data'] = $builder->getData()->getObjets()->toArray();
        }

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
            ))
            ->add('objets', 'choice', $objetsOptions)
            ->add('domaines', 'entity', [
                'class' => 'HopitalNumerique\DomaineBundle\Entity\Domaine',
                'label' => 'Domaines',
                'multiple' => true,
                'attr' => [
                    'class' => 'select2'
                ]
            ])
        ;
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'HopitalNumerique\ObjetBundle\Entity\Contenu'
            ))
            ->setRequired(['domaine'])
            ->setAllowedTypes([
                'domaine' => 'HopitalNumerique\DomaineBundle\Entity\Domaine'
            ])
        ;
    }

    public function getName()
    {
        return 'hopitalnumerique_objet_contenu';
    }
}