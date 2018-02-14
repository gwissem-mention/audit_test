<?php

namespace HopitalNumerique\ObjetBundle\Form;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ObjetBundle\Manager\Form\ObjetManagerForm;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ObjetType
 */
class ObjetType extends AbstractType
{
    private $_constraints = [];
    private $_userManager;

    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * @var objetManagerForm
     */
    private $objetManagerForm;

    /**
     * @var DomaineManager $domainManager
     */
    private $domainManager;

    /**
     * ObjetType constructor.
     *
     * @param                  $manager
     * @param                  $validator
     * @param UserManager      $userManager
     * @param ReferenceManager $referenceManager
     * @param ObjetManagerForm $objetManagerForm
     * @param DomaineManager   $domainManager
     */
    public function __construct(
        $manager,
        $validator,
        UserManager $userManager,
        ReferenceManager $referenceManager,
        ObjetManagerForm $objetManagerForm,
        DomaineManager $domainManager
    ) {
        $this->_constraints = $manager->getConstraints($validator);
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
        $this->objetManagerForm = $objetManagerForm;
        $this->domainManager = $domainManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'];
        $connectedUser = $this->_userManager->getUserConnected();

        /**
         * @var Objet
         */
        $objet = $builder->getData();

        $builder
            ->add('titre', TextType::class, [
                'max_length' => $this->_constraints['titre']['maxlength'],
                'required' => true,
                'label' => 'Titre',
                'attr' => ['class' => $this->_constraints['titre']['class']],
            ])
            ->add('alias', TextType::class, [
                'max_length' => $this->_constraints['alias']['maxlength'],
                'required' => true,
                'label' => 'Alias',
                'attr' => ['class' => $this->_constraints['alias']['class']],
            ])
            ->add('etat', EntityType::class, [
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
                ->add('source', TextType::class, [
                    'required' => false,
                    'max_length' => $this->_constraints['source']['maxlength'],
                    'label' => 'Source (si externe)',
                    'attr' => ['class' => $this->_constraints['source']['class']],
                ])
                ->add('cibleDiffusion', EntityType::class, [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('CIBLE_DIFFUSION'),
                    'property' => 'libelle',
                    'required' => false,
                    'label' => 'Cible de diffusion',
                ])
                ->add('communautePratiqueGroupe', EntityType::class, [
                    'class' => 'HopitalNumeriqueCommunautePratiqueBundle:Groupe',
                    'label' => 'Groupe de la communauté de partique associé',
                    'required' => false,
                ])
            ;
        }
        $builder
            ->add('roles', EntityType::class, [
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
                'attr' => ['placeholder' => 'Selectionnez le ou les catégories de cette publication'],
                'query_builder' => function (EntityRepository $er) use ($datas) {
                    $qb = $er->createQueryBuilder('ref')
                        ->leftJoin('ref.codes', 'codes')
                    ;

                    //cas objet existe + is ARTICLE
                    if ($datas->isArticle()) {
                        $qb->andWhere('ref.id != 188', 'ref.id != 570', 'codes.label = :article')
                           ->setParameter('article', 'CATEGORIE_ARTICLE');
                    //cas objet existe + is OBJET
                    } elseif (!$datas->isArticle()) {
                        $qb->andWhere('ref.id != 175', 'codes.label = :objet')
                           ->setParameter('objet', 'CATEGORIE_OBJET');
                    }

                    $qb->orderBy('ref.order', 'ASC');

                    return $qb;
                },
            ])
            ->add('synthese', TextareaType::class, [
                'required' => false,
                'label' => 'Synthèse',
                'attr' => ['class' => 'tinyMce'],
            ])
            ->add('resume', TextareaType::class, [
                'required' => true,
                'label' => 'Résumé',
                'attr' => ['class' => 'tinyMce ' . $this->_constraints['resume']['class']],
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'label' => 'Fichier 1',
            ])
            ->add('path', HiddenType::class)
            ->add('file2', FileType::class, [
                'required' => false,
                'label' => 'Fichier 2',
            ])
            ->add('path2', HiddenType::class)
            ->add('vignette', TextType::class, [
                'required' => false,
                'label' => 'Vignette',
                'attr' => ['readonly' => 'readonly'],
            ])
            ->add('ambassadeurs', EntityType::class, [
                'class' => 'HopitalNumeriqueUserBundle:User',
                'property' => 'nomPrenom',
                'required' => false,
                'multiple' => true,
                'label' => 'Ambassadeurs / Experts concernés',
                'attr' => ['placeholder' => 'Selectionnez le ou les ambassadeurs/Experts qui sont concernés par cette publication'],
                'choices' => $this->objetManagerForm->getConcernesChoices(),
            ])
            ->add('alaune', CheckboxType::class, [
              'required' => false,
              'label' => 'À la une ?',
              'label_attr' => [
                'class' => 'col-md-7 control-label',
              ],
              'attr' => ['class' => 'checkbox'],
            ])
            ->add('commentaires', CheckboxType::class, [
                'required' => false,
                'label' => 'Commentaires autorisés',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('btnSociaux', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher les boutons de partage',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('associatedProductions', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher les ressources associées',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('publicationPlusConsulte', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher dans les publications les plus vues',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('notes', CheckboxType::class, [
                'required' => false,
                'label' => 'Notes autorisées',
                'label_attr' => [
                    'class' => 'col-md-7 control-label',
                ],
                'attr' => ['class' => 'checkbox'],
            ])
            ->add('dateCreation', 'genemu_jquerydate', [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'label_attr' => [
                    'class' => 'col-md-3 control-label',
                ],
            ])
            ->add('releaseDate', 'genemu_jquerydate', [
                'label' => 'Date de parution',
                'widget' => 'single_text',
                'label_attr' => [
                    'class' => 'col-md-3 control-label',
                ],
            ])
            ->add('dateModification', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de dernière modification notifiée',
                'attr' => ['readonly' => 'readonly'],
                'label_attr' => [
                    'class' => 'col-md-3 control-label',
                ],
            ])
            ->add('domaines', EntityType::class, [
                'class' => Domaine::class,
                'multiple' => true,
                'choice_attr' => function (Domaine $domain) use ($connectedUser) {
                    if (!$connectedUser->getDomaines()->contains($domain)) {
                        return ['disabled' => 'disabled'];
                    }

                    return [];
                },
            ])
            ->add('modified', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('reason', TextType::class, [
               'mapped' => false,
               'attr' => [
                   'placeholder' => 'Raison de la mise à jour',
                   'class' => 'validate[required]',
               ],
            ])
            ->add('article', HiddenType::class)
        ;

        $builder->get('domaines')->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $formEvent) use ($connectedUser) {
                $objectDomains = $formEvent->getForm()->getData();
                $selectedData  = is_null($formEvent->getData()) ? [] : $formEvent->getData();

                $allowedDomains = $connectedUser->getDomaines()->toArray();

                $finalDomainList = [];

                // Get all object's domains the user doesn't have access to
                foreach ($objectDomains as $objectDomain) {
                    if (!in_array($objectDomain, $allowedDomains)) {
                        $finalDomainList[] = $objectDomain->getId();
                    }
                }

                // Adds user-selected domains
                foreach ($selectedData as $domainId) {
                    if (!is_null($domainId) && !in_array($domainId, $finalDomainList)) {
                        $finalDomainList[] = $domainId;
                    }
                }

                $formEvent->setData($finalDomainList);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Objet::class,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_objet_objet';
    }
}
