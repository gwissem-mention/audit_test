<?php

namespace HopitalNumerique\ModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionType extends AbstractType
{
    private $_constraints = [];
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    public function __construct($manager, $validator, ReferenceManager $referenceManager)
    {
        $this->_constraints = $manager->getConstraints($validator);
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateSession', 'genemu_jquerydate', [
                'required' => true,
                'label' => 'Début de la session',
                'widget' => 'single_text',
                'attr' => ['class' => $this->_constraints['dateSession']['class']],
            ])
            ->add('dateOuvertureInscription', 'genemu_jquerydate', [
                'required' => true,
                'label' => 'Date d\'ouverture des inscriptions',
                'widget' => 'single_text',
                'attr' => ['class' => $this->_constraints['dateOuvertureInscription']['class']],
            ])
            ->add('dateFermetureInscription', 'genemu_jquerydate', [
                'required' => true,
                'label' => 'Date de fermeture des inscriptions',
                'widget' => 'single_text',
                'attr' => ['class' => $this->_constraints['dateFermetureInscription']['class']],
            ])
            ->add('duree', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('DUREE_FORMATION'),
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Durée',
                    'empty_value' => ' - ',
                    'attr' => ['class' => $this->_constraints['duree']['class']],
            ])
            ->add('horaires', 'textarea', [
                    'required' => true,
                    'label' => 'Horaires',
                    'attr' => [
                            'rows' => 2,
                            'class' => $this->_constraints['horaires']['class'],
                    ],
            ])
            ->add('lieu', 'textarea', [
                    'required' => true,
                    'label' => 'Lieu',
                    'attr' => [
                            'rows' => 2,
                            'class' => $this->_constraints['lieu']['class'],
                    ],
            ])
            ->add('description', 'textarea', [
                    'required' => true,
                    'label' => 'Description',
                    'attr' => [
                            'rows' => 3,
                            'class' => $this->_constraints['description']['class'],
                    ],
            ])
            ->add('nombrePlaceDisponible', 'text', [
                'max_length' => 255,
                'required' => false,
                'label' => 'Nombre de places disponibles',
                'attr' => ['class' => $this->_constraints['nombrePlaceDisponible']['class']],
            ])
            ->add('formateur', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueUserBundle:User',
                    'property' => 'appellation',
                    'multiple' => false,
                    'required' => false,
                    'label' => 'Formateur',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('user')
                            ->where('user.enabled = ' . 1)
                            ->orderBy('user.nom', 'ASC');
                    },
            ])
            ->add('restrictionAcces', 'genemu_jqueryselect2_entity', [
                    'class' => 'NodevoRoleBundle:Role',
                    'property' => 'name',
                    'multiple' => true,
                    'required' => false,
                    'label' => 'Autoriser ce module à',
                    'empty_value' => ' - ',
                    'attr' => ['class' => 'restriction-acces'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('role')
                        ->where('role.etat = :actif')
                        ->setParameter('actif', 3)
                        ->orderBy('role.name', 'ASC');
                    },
            ])
            ->add('connaissances', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('CONNAISSANCES_AMBASSADEUR_SI'),
                    'property' => 'libelle',
                    'multiple' => true,
                    'required' => false,
                    //'group_by'      => 'parentName',
                    'label' => 'Connaissances SI',
                    'empty_value' => ' - ',
                    'attr' => ['class' => 'connaissances'],
            ])
            ->add('connaissancesMetier', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS'),
                    'property' => 'libelle',
                    'multiple' => true,
                    'required' => false,
                    // 'group_by'      => 'parentName',
                    'label' => 'Connaissances métiers',
                    'empty_value' => ' - ',
                    'attr' => ['class' => 'connaissancesMetier'],
            ])
            ->add('textMailRappel', 'textarea', [
                    'required' => false,
                    'label' => 'Texte du mail de rappel',
                    'attr' => [
                            'rows' => 2,
                    ],
            ])
            ->add('file', 'file', [
                    'required' => false,
                    'label' => 'Fiche de présence',
            ])
            ->add('path', 'hidden')
            ->add('etat', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('STATUT_SESSION_FORMATION'),
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Etat',
                    'empty_value' => ' - ',
                    'attr' => ['class' => $this->_constraints['etat']['class']],
            ])
            ->add('archiver', 'checkbox', [
                'required' => false,
                'label' => 'Archiver la session ?',
                'attr' => ['class' => 'checkbox'],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ModuleBundle\Entity\Session',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_module_session';
    }
}
