<?php

namespace HopitalNumerique\ReferenceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ReferenceType extends AbstractType
{
    private $_constraints = array();
    private $_userManager;

    public function __construct($manager, $validator, $userManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'];
        $connectedUser = $this->_userManager->getUserConnected();

        //code
        $attrCode = array('class' => $this->_constraints['code']['class']);
        if( $datas->getLock() )
            $attrCode['readonly'] = 'readonly';
        //parent
        $attrParent = array();
        if( $datas->getLock() )
            $attrParent['disabled'] = 'disabled';

        $id = $datas->getId();

        $builder
            ->add('libelle', 'text', array(
                'max_length' => $this->_constraints['libelle']['maxlength'],
                'required'   => true, 
                'label'      => 'Libellé',
                'attr'       => array('class' => $this->_constraints['libelle']['class'] )
            ))
            ->add('code', 'text', array(
                //'class'      => 'HopitalNumeriqueReferenceBundle:Reference',
                //'property'   => 'code',
                'max_length' => $this->_constraints['code']['maxlength'],
                'required'   => true, 
                'label'      => 'Code',
                'attr'       => $attrCode
            ))
            ->add('etat', 'entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'    => 'libelle',
                'required'    => true,
                'label'       => 'Etat',
                'attr'        => array('class' => $this->_constraints['etat']['class'] ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'ETAT')
                              ->orderBy('ref.order', 'ASC');
                }
            ))
            ->add('dictionnaire', 'checkbox', array(
                'required' => false,
                'label'    => 'Fait parti du dictionnaire de référencement',
                'attr'     => array( 'class'=> 'checkbox' )
            ))
            ->add('recherche', 'checkbox', array(
                'required' => false,
                'label'    => 'Présent dans les champs du moteur de recherche',
                'attr'     => array( 'class'=> 'checkbox' )
            ))
            ->add('parent', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'arboName',
                'required'      => false,
                'empty_value'   => ' - ',
                'label'         => 'Item parent',
                'attr'          => $attrParent,
                'query_builder' => function(EntityRepository $er) use ($id) {
                    $qb = $er->createQueryBuilder('ref')
                              ->andWhere('ref.lock = 0')
                              ->orderBy('ref.parent, ref.code, ref.order', 'ASC');
                    
                    if( $id )
                    {
                        $qb->andWhere("ref.id != $id");
                    }
                    
                    return $qb;
                }
            ))
            ->add('order', 'number', array(
                'required' => true, 
                'label'    => 'Ordre d\'affichage',
                'attr'     => array('class' => $this->_constraints['order']['class'] )
            ));

        if(count($datas->getChilds()) === 0)
        {
            $builder
                ->add('domaines', 'entity', array(
                    'class'       => 'HopitalNumeriqueDomaineBundle:Domaine',
                    'property'    => 'nom',
                    'required'    => false,
                    'multiple'    => true,
                    'label'       => 'Domaine(s) associé(s)',
                    'empty_value' => ' - ',
                    'query_builder' => function(EntityRepository $er) use ($connectedUser){
                        return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                    }
                )); 
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_reference_reference';
    }
}
