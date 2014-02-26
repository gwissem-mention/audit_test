<?php
namespace HopitalNumerique\ObjetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ObjetType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text', array(
                'max_length' => $this->_constraints['titre']['maxlength'],
                'required'   => true, 
                'label'      => 'Titre',
                'attr'       => array('class' => $this->_constraints['titre']['class'] )
            ))
            ->add('alias', 'text', array(
                'max_length' => $this->_constraints['alias']['maxlength'],
                'required'   => true, 
                'label'      => 'Alias',
                'attr'       => array('class' => $this->_constraints['alias']['class'] )
            ))
            ->add('etat', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Etat',
                'attr'          => array('class' => $this->_constraints['etat']['class'] ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'ETAT')
                              ->orderBy('ref.order', 'ASC');
                }
            ))
            ->add('roles', 'genemu_jqueryselect2_entity', array(
                'class'    => 'NodevoRoleBundle:Role',
                'property' => 'name',
                'multiple' => true,
                'required' => false,
                'label'    => 'Interdire l\'accès au groupes',
                'attr'     => array( 'placeholder' => 'Selectionnez le ou les rôles qui auront accès à cet objet' )
            ))
            ->add('types', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'multiple'      => true,
                'label'         => 'Type d\'objet',
                'group_by'      => 'parentName',
                'attr'          => array( 'placeholder' => 'Selectionnez le ou les types de cet objet' ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->andWhere('ref.code = :etat', 'ref.id != 175', 'ref.id != 176', 'ref.id != 179')
                              ->setParameter('etat', 'TYPE_OBJET')
                              ->orderBy('ref.parent, ref.order', 'ASC');
                }
            ))
            ->add('synthese', 'textarea', array(
                'required' => false, 
                'label'    => 'Synthèse'
            ))
            ->add('resume', 'textarea', array(
                'required' => true, 
                'label'    => 'Résumé',
                'attr'     => array('class' => $this->_constraints['resume']['class'] )
            ))
            ->add('file', 'file', array(
                'required' => false, 
                'label'    => 'Fichier objet 1'
            ))
            ->add('path', 'hidden')
            ->add('file2', 'file', array(
                'required' => false, 
                'label'    => 'Fichier objet 2'
            ))
            ->add('path2', 'hidden')
            ->add('references', 'entity', array(
                'class'    => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => false,
                'multiple' => true,
                'label'    => 'Référencement'
            ))
            ->add('ambassadeurs', 'entity', array(
                'class'    => 'HopitalNumeriqueUserBundle:User',
                'property' => 'prenomNom',
                'required' => false,
                'multiple' => true,
                'label'    => 'Ambassadeurs concernés',
                'attr'     => array( 'placeholder' => 'Selectionnez le ou les ambassadeurs qui sont concernés par cet objet' ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                              ->leftJoin('user.roles', 'role')
                              ->where('role.role = :ambassadeur')
                              ->setParameter('ambassadeur','ROLE_AMBASSADEUR_7');
                }
            ))
            ->add('commentaires', 'checkbox', array(
                'required'   => false,
                'label'      => 'Commentaires autorisés',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                )
            ))
            ->add('notes', 'checkbox', array(
                'required'   => false,
                'label'      => 'Notes autorisés',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                )
            ))
            ->add('dateCreation', 'genemu_jquerydate', array(
                'required'   => true, 
                'label'      => 'Date de création',
                'widget'     => 'single_text',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                )
            ))
            ->add('dateDebutPublication', 'genemu_jquerydate', array(
                'required'   => false, 
                'label'      => 'Début de publication',
                'widget'     => 'single_text',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                )
            ))
            ->add('dateFinPublication', 'genemu_jquerydate', array(
                'required'   => false, 
                'label'      => 'Fin de publication',
                'widget'     => 'single_text',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                )
            ))
            ->add('dateModification', 'date', array(
                'required'   => false, 
                'widget'     => 'single_text',
                'label'      => 'Date de dernière modification notifiée',
                'attr'       => array('readonly' => 'readonly'),
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                )
            ))
            ->add('modified', 'hidden', array(
                'mapped'   => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ObjetBundle\Entity\Objet'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_objet_objet';
    }
}