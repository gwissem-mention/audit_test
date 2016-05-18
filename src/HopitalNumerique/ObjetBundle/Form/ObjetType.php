<?php
namespace HopitalNumerique\ObjetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ObjetBundle\Manager\Form\ObjetManagerForm;

use Doctrine\ORM\EntityRepository;

class ObjetType extends AbstractType
{
    private $_constraints = array();
    private $_userManager;
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\Form\objetManagerForm
     */
    private $objetManagerForm;

    public function __construct($manager, $validator, UserManager $userManager, ReferenceManager $referenceManager, ObjetManagerForm $objetManagerForm)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
        $this->objetManagerForm = $objetManagerForm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'] ;
        $connectedUser = $this->_userManager->getUserConnected();

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
            ->add('source', 'text', array(
                'required'   => false,
                'max_length' => $this->_constraints['source']['maxlength'],
                'label'      => 'Source (si externe)',
                'attr'       => array('class' => $this->_constraints['source']['class'] )
            ))
            ->add('etat', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices'       => $this->referenceManager->findByCode('ETAT'),
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Etat',
                'attr'          => array('class' => $this->_constraints['etat']['class'] ),
            ))
            ->add('cibleDiffusion', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices'       => $this->referenceManager->findByCode('CIBLE_DIFFUSION'),
                'property'      => 'libelle',
                'required'      => false,
                'label'         => 'Cible de diffusion',
            ))
            ->add('roles', 'entity', array(
                'class'    => 'NodevoRoleBundle:Role',
                'property' => 'name',
                'multiple' => true,
                'required' => false,
                'label'    => 'Interdire l\'accès au(x) groupe(s)',
                'attr'     => array( 'placeholder' => 'Selectionnez le ou les rôles qui n\'auront pas accès à cette publication' )
            ))
            ->add('types', 'genemu_jqueryselect2_entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'multiple'      => true,
                'label'         => 'Catégorie',
                'group_by'      => 'parentName',
                'attr'          => array( 'placeholder' => 'Selectionnez le ou les catégories de cette publication' ),
                'query_builder' => function(EntityRepository $er) use ($datas) {
                    $qb = $er->createQueryBuilder('ref');

                    //cas objet existe + is ARTICLE
                    if( $datas->isArticle() ){
                        $qb->andWhere('ref.id != 188','ref.id != 570','ref.code = :article')
                           ->setParameter('article', 'CATEGORIE_ARTICLE');
                    //cas objet existe + is OBJET
                    }elseif( !$datas->isArticle() ) {
                        $qb->andWhere('ref.id != 175','ref.code = :objet')
                           ->setParameter('objet', 'CATEGORIE_OBJET');
                    }

                    $qb->orderBy('ref.parent, ref.order', 'ASC');

                    return $qb;
                }
            ))
            ->add('synthese', 'textarea', array(
                'required' => false,
                'label'    => 'Synthèse',
                'attr'     => array('class' => 'tinyMce')
            ))
            ->add('resume', 'textarea', array(
                'required' => true,
                'label'    => 'Résumé',
                'attr'     => array('class' => 'tinyMce '.$this->_constraints['resume']['class'] )
            ))
            ->add('file', 'file', array(
                'required' => false,
                'label'    => 'Fichier 1'
            ))
            ->add('path', 'hidden')
            ->add('file2', 'file', array(
                'required' => false,
                'label'    => 'Fichier 2'
            ))
            ->add('path2', 'hidden')
            ->add('vignette', 'text', array(
                'required' => false,
                'label'    => 'Vignette',
                'attr'     => array('readonly'=>'readonly')
            ))
            ->add('references', 'entity', array(
                'class'    => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => false,
                'multiple' => true,
                'label'    => 'Référencement'
            ))
            ->add('ambassadeurs', 'entity', array(
                'class'    => 'HopitalNumeriqueUserBundle:User',
                'property' => 'nomPrenom',
                'required' => false,
                'multiple' => true,
                'label'    => 'Ambassadeurs / Experts concernés',
                'attr'     => array( 'placeholder' => 'Selectionnez le ou les ambassadeurs/Experts qui sont concernés par cette publication' ),
                'choices'  => $this->objetManagerForm->getConcernesChoices(),
            ))
            ->add('alaune', 'checkbox', array(
              'required'   => false,
              'label'      => 'À la une ?',
              'label_attr' => array(
                'class' => 'col-md-7 control-label'
              ),
              'attr'       => array( 'class'=> 'checkbox' )
            ))
            ->add('commentaires', 'checkbox', array(
                'required'   => false,
                'label'      => 'Commentaires autorisés',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                ),
                'attr'       => array( 'class'=> 'checkbox' )
            ))
            ->add('btnSociaux', 'checkbox', array(
                'required'   => false,
                'label'      => 'Afficher les boutons de partage',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                ),
                'attr'       => array( 'class'=> 'checkbox' )
            ))
            ->add('publicationPlusConsulte', 'checkbox', array(
                'required'   => false,
                'label'      => 'Afficher dans les plus consultées',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                ),
                'attr'       => array( 'class'=> 'checkbox' )
            ))
            ->add('notes', 'checkbox', array(
                'required'   => false,
                'label'      => 'Notes autorisées',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                ),
                'attr'       => array( 'class'=> 'checkbox' )
            ))
            ->add('dateCreation', 'genemu_jquerydate', array(
                'required'   => true,
                'label'      => 'Date de création',
                'widget'     => 'single_text',
                'label_attr' => array(
                    'class' => 'col-md-7 control-label'
                )
            ))
            ->add('dateParution', 'text', array(
                'required'   => false,
                'label'      => 'Début de parution',
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
            ))
            ->add('communautePratiqueGroupe', 'entity', array(
                'class' => 'HopitalNumeriqueCommunautePratiqueBundle:Groupe',
                'label' => 'Groupe de la communauté de partique associé',
                'required' => false
            ))
            ->add('modified', 'hidden', array(
                'mapped'   => false
            ))
            ->add('article', 'hidden');
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
