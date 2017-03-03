<?php

namespace HopitalNumerique\ModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleType extends AbstractType
{
    private $_constraints = [];
    private $_userManager;
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    public function __construct($manager, $validator, UserManager $userManager, ReferenceManager $referenceManager)
    {
        $this->_constraints = $manager->getConstraints($validator);

        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->_userManager->getUserConnected();

        $builder
            ->add('titre', 'text', [
                    'max_length' => $this->_constraints['titre']['maxlength'],
                    'required' => true,
                    'label' => 'Titre du module',
                    'attr' => [
                        'class' => $this->_constraints['titre']['class'],
                    ],
            ])
            ->add('productions', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueObjetBundle:Objet',
                    'property' => 'titre',
                    'multiple' => true,
                    'required' => false,
                    'label' => 'Productions concernées',
                    'empty_value' => ' - ',
                    'attr' => ['class' => 'productions'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->getProductionsActive();
                    },
            ])
            ->add('connaissances', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property' => 'libelle',
                    'multiple' => true,
                    'required' => false,
                    //'group_by'      => 'parentName',
                    'label' => 'Connaissances SI',
                    'empty_value' => ' - ',
                    'attr' => ['class' => 'connaissances'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->where('ref.code = :etat')
                            ->setParameter('etat', 'CONNAISSANCES_AMBASSADEUR_SI')
                            ->orderBy('ref.order', 'ASC')
                        ;
                    },
            ])
            ->add('connaissancesMetier', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property' => 'libelle',
                    'multiple' => true,
                    'required' => false,
                    // 'group_by'      => 'parentName',
                    'label' => 'Connaissances métiers',
                    'empty_value' => ' - ',
                    'attr' => ['class' => 'connaissancesMetier'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->where('ref.code = :etat')
                            ->setParameter('etat', 'PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS')
                            ->orderBy('ref.order', 'ASC');
                    },
            ])
            ->add('domaines', 'entity', [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property' => 'nom',
                'required' => false,
                'multiple' => true,
                'label' => 'Domaine(s) associé(s)',
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                },
            ])
            ->add('duree', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('DUREE_FORMATION'),
                    'property' => 'libelle',
                    'required' => false,
                    'label' => 'Durée',
                    'empty_value' => ' - ',
                    'attr' => [],
            ])
            ->add('horairesType', 'text', [
                    'max_length' => $this->_constraints['horairesType']['maxlength'],
                    'required' => false,
                    'label' => 'Horaires type',
                    'attr' => [
                            'class' => $this->_constraints['horairesType']['class'],
                    ],
            ])
            ->add('lieu', 'textarea', [
                    'required' => false,
                    'label' => 'Lieu',
                    'attr' => [
                            'rows' => 3,
                    ],
            ])
            ->add('description', 'textarea', [
                    'required' => false,
                    'label' => 'Description',
                    'attr' => [
                            'rows' => 3,
                    ],
            ])
            ->add('nombrePlaceDisponible', 'integer', [
                    'required' => false,
                    'label' => 'Nombre de places disponibles',
                    'attr' => [
                            'class' => $this->_constraints['nombrePlaceDisponible']['class'],
                    ],
            ])
            ->add('prerequis', 'textarea', [
                    'required' => false,
                    'label' => 'Prérequis',
                    'attr' => [
                            'rows' => 3,
                    ],
            ])
            ->add('formateur', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueUserBundle:User',
                    'property' => 'appellation',
                    'multiple' => false,
                    'required' => false,
                    'label' => 'Formateur',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                        return $er->createQueryBuilder('user')
                            ->leftJoin('user.domaines', 'domaine')
                            ->where('domaine.id IN (:domainesIds)')
                                ->setParameter('domainesIds', $connectedUser->getDomainesId())
                            ->andWhere('user.enabled = ' . 1)
                            ->orderBy('user.nom', 'ASC');
                    },
            ])
            ->add('textMailRappel', 'textarea', [
                    'required' => false,
                    'label' => 'Texte du mail de rappel',
                    'attr' => [
                            'rows' => 3,
                    ],
            ])

            ->add('mailAccuseInscription', 'checkbox', [
                'required' => false,
                'label' => 'Envoyer le mail d\'accusé de réception d\'inscription ?',
            ])

            ->add('mailConfirmationInscription', 'checkbox', [
                'required' => false,
                'label' => 'Envoyer le mail de confirmation d\'inscription ?',
            ])

            ->add('mailRefusInscription', 'checkbox', [
                'required' => false,
                'label' => 'Envoyer le mail de refus ?',
            ])

            ->add('mailRappelEvalution', 'checkbox', [
                'required' => false,
                'label' => 'Envoyer le mail de rappel d\'évalution ?',
            ])

            ->add('mailAlerteEvaluation', 'checkbox', [
                'required' => false,
                'label' => 'Envoyer le mail d\'alerte pour l\'évaluation ?',
            ])

            ->add('file', 'file', [
                    'required' => false,
                    'label' => 'Pièce-jointe',
            ])
            ->add('path', 'hidden')
            ->add('statut', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('ETAT'),
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Statut',
                    'empty_value' => ' - ',
                    'attr' => ['class' => $this->_constraints['statut']['class']],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ModuleBundle\Entity\Module',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_module_module';
    }
}
