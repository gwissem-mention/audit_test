<?php

namespace HopitalNumerique\EtablissementBundle\Form;

use Nodevo\ToolsBundle\Manager\Manager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

class EtablissementType extends AbstractType
{
    private $constraints = [];
    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * EtablissementType constructor.
     *
     * @param Manager          $manager
     * @param                  $validator
     * @param ReferenceManager $referenceManager
     */
    public function __construct($manager, $validator, ReferenceManager $referenceManager)
    {
        $this->constraints      = $manager->getConstraints($validator);
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'max_length' => $this->constraints['nom']['maxlength'],
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => $this->constraints['nom']['class']],
            ])
            ->add('finess', TextType::class, [
                'max_length' => $this->constraints['finess']['maxlength'],
                'required' => true,
                'label' => 'FINESS Geographique',
                'attr' => ['class' => $this->constraints['finess']['class']],
            ])
            ->add('typeOrganisme', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Type de structure',
                'empty_value' => ' - ',
                'attr' => ['class' => $this->constraints['typeOrganisme']['class']],
            ])
            ->add('region', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('REGION'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Région',
                'empty_value' => ' - ',
                'attr' => ['class' => $this->constraints['region']['class']],
            ])
            ->add('departement', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Département',
                'empty_value' => ' Sélectionnez une région avant le département ',
                'attr' => ['class' => $this->constraints['departement']['class']],
            ])
            ->add('adresse', TextareaType::class, [
                'max_length' => $this->constraints['adresse']['maxlength'],
                'required' => true,
                'label' => 'Adresse',
                'attr' => ['class' => $this->constraints['adresse']['class']],
            ])
            ->add('ville', TextType::class, [
                'max_length' => $this->constraints['ville']['maxlength'],
                'required' => true,
                'label' => 'Ville',
                'attr' => ['class' => $this->constraints['ville']['class']],
            ])
            ->add('codepostal', TextType::class, [
                'max_length' => $this->constraints['codepostal']['maxlength'],
                'required' => true,
                'label' => 'Code Postal',
                'attr' => ['class' => $this->constraints['codepostal']['class']],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
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
