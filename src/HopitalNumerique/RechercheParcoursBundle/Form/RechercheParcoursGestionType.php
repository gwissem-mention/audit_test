<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Doctrine\ORM\EntityRepository;

class RechercheParcoursGestionType extends AbstractType
{
    /**
     * @var \HopitalNumerique\CoreBundle\DependencyInjection\Entity Entity
     */
    private $entity;

    private $_userManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    public function __construct($manager, $validator, Entity $entity, UserManager $userManager, ReferenceManager $referenceManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->entity = $entity;
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->_userManager->getUserConnected();
        $isCreation = (null === $builder->getData()->getId());

        $builder
            ->add('nom', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Nom',
                'attr'        => array('class' => 'validate[required]')
            ))
            ->add('domaines', 'entity', array(
                'class'       => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property'    => 'nom',
                'required'    => true,
                'multiple'    => true,
                'label'       => 'Domaine(s) associé(s)',
                'empty_value' => ' - ',
                'query_builder' => function(EntityRepository $er) use ($connectedUser){
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                }
            ))
            ->add('typePublication', 'genemu_jqueryselect2_entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'    => 'libelle',
                'required'    => true,
                'multiple'    => true,
                'label'       => 'Type de publication à afficher',
                'empty_value' => ' - ',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('ref')
                        ->leftJoin('ref.parents', 'parent')
                        ->where('ref.code = :code')
                        ->andWhere('parent.id IS NULL')
                        ->setParameter('code', 'CATEGORIE_OBJET');
                }
            ))
        ;
        if (!$isCreation) {
            $domaines = $this->entity->getEntityDomainesCommunsWithUser($builder->getData(), $connectedUser);
            $references = $this->referenceManager->findByDomaines($domaines, true, null, null, true);

            $builder
                ->add('referencesParentes', 'genemu_jqueryselect2_entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $references,
                    'property'    => 'libelle',
                    'required'    => true,
                    'multiple'    => true,
                    'label'       => 'Référence(s) parente(s)',
                    //'group_by'    => 'parent',
                    'empty_value' => ' - '
                ))
                ->add('referencesVentilations', 'genemu_jqueryselect2_entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $references,
                    'property'    => 'libelle',
                    'required'    => true,
                    'multiple'    => true,
                    'label'       => 'Référence(s) de ventilation',
                    //'group_by'    => 'parent',
                    'empty_value' => ' - '
                ))
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_rechercheparcours_rechercheparcoursgestion';
    }
}
