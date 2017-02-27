<?php

namespace HopitalNumerique\EtablissementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

class EtablissementType extends AbstractType
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
            ->add('nom', 'text', [
                'max_length' => $this->_constraints['nom']['maxlength'],
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => $this->_constraints['nom']['class']],
            ])
            ->add('finess', 'text', [
                'max_length' => $this->_constraints['finess']['maxlength'],
                'required' => true,
                'label' => 'FINESS Geographique',
                'attr' => ['class' => $this->_constraints['finess']['class']],
            ])
            ->add('typeOrganisme', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Type d\'établissement',
                'empty_value' => ' - ',
                'attr' => ['class' => $this->_constraints['typeOrganisme']['class']],
            ])
            ->add('region', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('REGION'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Région',
                'empty_value' => ' - ',
                'attr' => ['class' => $this->_constraints['region']['class']],
            ])
            ->add('departement', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Département',
                'empty_value' => ' Sélectionnez une région avant le département ',
                'attr' => ['class' => $this->_constraints['departement']['class']],
            ])
            ->add('adresse', 'textarea', [
                'max_length' => $this->_constraints['adresse']['maxlength'],
                'required' => true,
                'label' => 'Adresse',
                'attr' => ['class' => $this->_constraints['adresse']['class']],
            ])
            ->add('ville', 'text', [
                'max_length' => $this->_constraints['ville']['maxlength'],
                'required' => true,
                'label' => 'Ville',
                'attr' => ['class' => $this->_constraints['ville']['class']],
            ])
            ->add('codepostal', 'text', [
                'max_length' => $this->_constraints['codepostal']['maxlength'],
                'required' => true,
                'label' => 'Code Postal',
                'attr' => ['class' => $this->_constraints['codepostal']['class']],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_etablissement_etablissement';
    }
}
