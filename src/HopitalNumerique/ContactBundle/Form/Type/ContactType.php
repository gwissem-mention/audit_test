<?php

namespace HopitalNumerique\ContactBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Nodevo\ContactBundle\Form\Type\ContactType as NodevoContactType;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ContactType extends NodevoContactType
{
    private $_constraints = array();

    public function __construct($manager, $validator, $securityContext)
    {
        parent::__construct($manager, $validator, $securityContext);

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
        parent::buildForm($builder, $options);
        
        $user = $this->_securityContext->getToken()->getUser();
        
        $builder

        ->add('civilite', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Civilite',
                'empty_value'   => ' - ',
                'attr'          => array('class' => $this->_constraints['civilite']['class'] ),
                'data'          => ('anon.' != $user && !is_null($user->getCivilite())) ? $user->getCivilite() : '',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'CIVILITE')
                        ->orderBy('ref.order', 'ASC');
                }
        ))

        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ContactBundle\Entity\Contact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopital_numerique_contact_contact';
    }
}
