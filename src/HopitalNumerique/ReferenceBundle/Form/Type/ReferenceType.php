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
        $connectedUser = $this->_userManager->getUserConnected();

        if ($connectedUser->hasRoleAdmin()) {
            if (count($options['data']->getEnfants()) === 0) {
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
                        },
                        'attr' => [
                            'class' => 'select2'
                        ]
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

            $this->buildFormPartConcept($builder, $options);
            $this->buildFormPartListe($builder, $options);
            $this->buildFormPartReference($builder, $options);
        }
        $this->buildFormPartGlossaire($builder, $options);
    }

    /**
     * Construit la partie Concept du formulaire.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Builder
     * @param array                                        $options Options
     */
    private function buildFormPartConcept(FormBuilderInterface $builder, array $options)
    {
        $parentAttr = [];
        if ($options['data']->getLock()) {
            $parentAttr['disabled'] = 'disabled';
        }
        $referenceId = $options['data']->getId();

        $builder
            ->add('libelle', 'text', array(
                'required' => true,
                'label' => 'Libellé du concept',
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]'
                ]
            ))
            ->add('image', 'hidden', [
                'required' => false
            ])
            ->add('imageFile', 'file', array(
                'label' => 'Image',
                'required' => false
            ))
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $this->verifyImage($event->getForm(), $event->getData());
            })
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
            ->add('parents', 'entity', array(
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'multiple' => true,
                'required' => false,
                'label' => 'Parents',
                'attr' => $parentAttr,
                'query_builder' => function(EntityRepository $er) use ($referenceId) {
                    $qb = $er->createQueryBuilder('ref')
                              ->andWhere('ref.lock = 0')
                        ->leftJoin('ref.parents', 'parent')
                              ->orderBy('parent.id, ref.code, ref.order', 'ASC');

                    if( $referenceId )
                    {
                        $qb->andWhere("ref.id != $referenceId");
                    }

                    return $qb;
                }
            ))
            ->add('etat', 'entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices'     => $this->referenceManager->findByCode('ETAT'),
                'property'    => 'libelle',
                'required'    => true,
                'label'       => 'Etat',
                'attr'        => array('class' => $this->_constraints['etat']['class'] ),
            ))
        ;
    }

    /**
     * Construit la partie Liste du formulaire.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Builder
     * @param array                                        $options Options
     */
    private function buildFormPartListe(FormBuilderInterface $builder, array $options)
    {
        $attrCode = array(
            'maxlength' => 255
        );
        if ($options['data']->getLock())
            $attrCode['readonly'] = 'readonly';

        $builder
            ->add('code', 'text', array(
                'required'   => false,
                'label'      => 'Code',
                'attr'       => $attrCode
            ))
            ->add('order', 'number', array(
                'required' => true,
                'label'    => 'Ordre d\'affichage',
                'attr'     => array('class' => $this->_constraints['order']['class'] )
            ))
        ;
    }

    /**
     * Construit la partie Référence du formulaire.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Builder
     * @param array                                        $options Options
     */
    private function buildFormPartReference(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', 'checkbox', array(
                'required' => false,
                'label' => 'Est une référence ?'
            ))
            ->add('parentable', 'checkbox', array(
                'required' => false,
                'label' => 'Peut être parent ?'
            ))
            ->add('inRecherche', 'checkbox', array(
                'required' => false,
                'label' => 'Présente dans la recherche ?'
            ))
            ->add('referenceLibelle', 'text', array(
                'required' => false,
                'label' => 'Libellé de la référence (si différent du libellé du concept)',
                'attr' => [
                    'maxlength' => 255
                ]
            ))
        ;
    }

    /**
     * Construit la partie Glossaire du formulaire.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Builder
     * @param array                                        $options Options
     */
    private function buildFormPartGlossaire(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('inGlossaire', 'checkbox', array(
                'required' => false,
                'label' => 'Présent dans le glossaire ?'
            ))
            ->add('sigle', 'text', array(
                'required' => false,
                'label' => 'Sigle',
                'attr' => [
                    'maxlength' => 255
                ]
            ))
            ->add('glossaireLibelle', 'text', array(
                'required' => false,
                'label' => 'Libellé dans le glossaire (si différent du libellé du concept)',
                'attr' => [
                    'maxlength' => 255
                ]
            ))
            ->add('descriptionCourte', 'textarea', array(
                'required' => false,
                'label' => 'Description courte <span title="Ce champ est requis" style="color:red;font-size:10px">*</span>'
            ))
            ->add('descriptionLongue', 'textarea', array(
                'required' => false,
                'label' => 'Description longue',
                'attr' => [
                    'class' => 'tinyMce'
                ]
            ))
            ->add('casseSensible', 'checkbox', array(
                'required' => false,
                'label' => 'Sensible à la casse ?'
            ))
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
