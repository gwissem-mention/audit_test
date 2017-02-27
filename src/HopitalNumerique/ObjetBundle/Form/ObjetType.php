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
    private $_constraints = [];
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
        $this->_constraints = $manager->getConstraints($validator);
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
        $this->objetManagerForm = $objetManagerForm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'];
        $connectedUser = $this->_userManager->getUserConnected();

        /**
         * @var \HopitalNumerique\ObjetBundle\Entity\Objet
         */
        $objet = $builder->getData();

        $builder
            ->add('titre', 'text', [
                'max_length' => $this->_constraints['titre']['maxlength'],
                'required' => true,
                'label' => 'Titre',
                'attr' => ['class' => $this->_constraints['titre']['class']],
            ])
            ->add('alias', 'text', [
                'max_length' => $this->_constraints['alias']['maxlength'],
                'required' => true,
                'label' => 'Alias',
                'attr' => ['class' => $this->_constraints['alias']['class']],
            ])
            ->add('etat', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('ETAT'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Etat',
                'attr' => ['class' => $this->_constraints['etat']['class']],
            ])
        ;
        if (!$objet->isArticle()) {
            $builder
                ->add('source', 'text', [
                    'required' => false,
                    'max_length' => $this->_constraints['source']['maxlength'],
                    'label' => 'Source (si externe)',
                    'attr' => ['class' => $this->_constraints['source']['class']],
                ])
                ->add('cibleDiffusion', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('CIBLE_DIFFUSION'),
                    'property' => 'libelle',
                    'required' => false,
                    'label' => 'Cible de diffusion',
                ])
                ->add('communautePratiqueGroupe', 'entity', [
                    'class' => 'HopitalNumeriqueCommunautePratiqueBundle:Groupe',
                    'label' => 'Groupe de la communauté de partique associé',
                    'required' => false,
                ])
            ;
        }
        $builder
            ->add('roles', 'entity', [
                'class' => 'NodevoRoleBundle:Role',
                'property' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'Interdire l\'accès au(x) groupe(s)',
                'attr' => ['placeholder' => 'Selectionnez le ou les rôles qui n\'auront pas accès à cette publication'],
            ])
            ->add('types', 'genemu_jqueryselect2_entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => true,
                'multiple' => true,
                'label' => 'Catégorie',
                //'group_by'      => 'parentName',
                'attr' => ['placeholder' => 'Selectionnez le ou les catégories de cette publication'],
                'query_builder' => function (EntityRepository $er) use ($datas) {
                    $qb = $er->createQueryBuilder('ref');

                    //cas objet existe + is ARTICLE
                    if ($datas->isArticle()) {
                        $qb->andWhere('ref.id != 188', 'ref.id != 570', 'ref.code = :article')
                           ->setParameter('article', 'CATEGORIE_ARTICLE');
                    //cas objet existe + is OBJET
                    } elseif (!$datas->isArticle()) {
                        $qb->andWhere('ref.id != 175', 'ref.code = :objet')
                           ->setParameter('objet', 'CATEGORIE_OBJET');
                    }

                    $qb->orderBy('ref.order', 'ASC');

                    return $qb;
                },
            ])
            ->add('synthese', 'textarea', [
                'required' => false,
                'label' => 'Synthèse',
                'attr' => ['class' => 'tinyMce'],
            ])
            ->add('resume', 'textarea', [
                'required' => true,
                'label' => 'Résumé',
                'attr' => ['class' => 'tinyMce ' . $this->_constraints['resume']['class']],
            ])
            ->add('file', 'file', [
                'required' => false,
                'label' => 'Fichier 1',
            ])
            ->add('path', 'hidden')
            ->add('file2', 'file', [
                'required' => false,
                'label' => 'Fichier 2',
            ])
            ->add('path2', 'hidden')
            ->add('vignette', 'text', [
                'required' => false,
                'label' => 'Vignette',
                'attr' => ['readonly' => 'readonly'],
            ])
            ->add('ambassadeurs', 'entity', [
                'class' => 'HopitalNumeriqueUserBundle:User',
                'property' => 'nomPrenom',
                'required' => false,
                'multiple' => true,
                'label' => 'Ambassadeurs / Experts concernés',
                'attr' => ['placeholder' => 'Selectionnez le ou les ambassadeurs/Experts qui sont concernés par cette publication'],
                'choices' => $this->objetManagerForm->getConcernesChoices(),
            ])
            ->add('alaune', 'checkbox', [
              'required' => false,
              'label' => 'À la une ?',
              'label_attr' => [
                'class' => 'col-md-7 control-label',
              ],
              'attr' => ['class' => 'checkbox'],
            ])
            ->add('commentaires', 'checkbox', [
                'required' => false,
                'label' => 'Commentaires autorisés',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('btnSociaux', 'checkbox', [
                'required' => false,
                'label' => 'Afficher les boutons de partage',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('associatedProductions', 'checkbox', [
                'required' => false,
                'label' => 'Afficher les ressources associées',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('publicationPlusConsulte', 'checkbox', [
                'required' => false,
                'label' => 'Afficher dans les plus consultées',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('notes', 'checkbox', [
                'required' => false,
                'label' => 'Notes autorisées',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('dateCreation', 'genemu_jquerydate', [
                'required' => true,
                'label' => 'Date de création',
                'widget' => 'single_text',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
            ])
            ->add('dateParution', 'text', [
                'required' => false,
                'label' => 'Début de parution',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
            ])
            ->add('dateModification', 'date', [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de dernière modification notifiée',
                'attr' => ['readonly' => 'readonly'],
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
            ])
            ->add('domaines', 'entity', [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property' => 'nom',
                'required' => true,
                'multiple' => true,
                'label' => 'Domaine(s) associé(s)',
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                },
            ])
            ->add('modified', 'hidden', [
                'mapped' => false,
            ])
            ->add('article', 'hidden');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ObjetBundle\Entity\Objet',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_objet_objet';
    }
}
