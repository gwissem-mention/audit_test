<?php
namespace HopitalNumerique\ReferenceBundle\Form\Type;

use Nodevo\ToolsBundle\Tools\Systeme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Doctrine\ORM\EntityRepository;

class ReferenceType extends AbstractType
{
    private $_constraints = array();
    private $_userManager;
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    public function __construct($manager, $validator, $userManager, ReferenceManager $referenceManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
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
        $attrParent = array(
            'class' => 'select2'
        );
        if( $datas->getLock() )
            $attrParent['disabled'] = 'disabled';

        $id = $datas->getId();

        if (count($datas->getEnfants()) === 0) {
            $builder
                ->add('domaines', 'entity', array(
                    'class'       => 'HopitalNumeriqueDomaineBundle:Domaine',
                    'property'    => 'nom',
                    'required'    => false,
                    'multiple'    => true,
                    'label'       => 'Domaine(s) associé(s)',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                        return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                    }
                ))
            ;

            if ($connectedUser->hasRoleAdmin()) {
                $builder
                    ->add('allDomaines', 'checkbox', [
                        'label' => 'Tous les domaines',
                        'required' => false
                    ])
                ;
            }
        }

        $this->buildFormPartConcept($builder);

        $builder
            ->add('code', 'text', array(
                'max_length' => $this->_constraints['code']['maxlength'],
                'required'   => true, 
                'label'      => 'Code',
                'attr'       => $attrCode
            ))
            ->add('etat', 'entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices'     => $this->referenceManager->findByCode('ETAT'),
                'property'    => 'libelle',
                'required'    => true,
                'label'       => 'Etat',
                'attr'        => array('class' => $this->_constraints['etat']['class'] ),
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
            ->add('parents', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                //'property'      => 'arboName',
                'multiple' => true,
                'required'      => false,
                'empty_value'   => ' - ',
                'label'         => 'Item parent',
                'attr'          => $attrParent,
                'query_builder' => function(EntityRepository $er) use ($id) {
                    $qb = $er->createQueryBuilder('ref')
                              ->andWhere('ref.lock = 0')
                        ->leftJoin('ref.parents', 'parent')
                              ->orderBy('parent.id, ref.code, ref.order', 'ASC');

                    if( $id )
                    {
                        $qb->andWhere("ref.id != $id");
                    }

                    return $qb;
                }
            ))
            ->add('image', 'hidden', [
                'required' => false
            ])
            ->add('imageFile', 'file', array(
                'label' => 'Image',
                'required' => false
            ))
            ->add('order', 'number', array(
                'required' => true, 
                'label'    => 'Ordre d\'affichage',
                'attr'     => array('class' => $this->_constraints['order']['class'] )
            ))
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $this->verifyImage($event->getForm(), $event->getData());
        });
    }

    /**
     * Construit la partie Concept du formulaire.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Builder
     */
    private function buildFormPartConcept(FormBuilderInterface $builder)
    {
        $builder
            ->add('libelle', 'text', array(
                'required' => true,
                'label' => 'Libellé du concept',
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]'
                ]
            ))
            ->add('synonymes', 'collection', [
                'label' => 'Synonymes',
                'type' => SynonymeType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true
            ])
            ->add('champLexicalNoms', 'collection', [
                'label' => 'Champ lexical',
                'type' => ChampLexicalNomType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true
            ])
        ;
    }

    /**
     * Vérifie la validité de l'image.
     *
     * @param \Symfony\Component\Form\FormInterface              $form      Formulaire
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence
     */
    private function verifyImage(FormInterface $form, Reference $reference)
    {
        if (null !== $reference->getImageFile() && !$reference->imageFileIsValid()) {
            $form->get('imageFile')->addError(new FormError('Veuillez choisir une image inférieure à '.intval(Systeme::getFileUploadMaxSize() / 1024 / 1024).' Mo.'));
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
