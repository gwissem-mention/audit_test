<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use Nodevo\FormBundle\Form\JQuery\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use HopitalNumerique\UserBundle\Entity\Contractualisation;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Nodevo\ToolsBundle\Manager\Manager;
use Doctrine\ORM\EntityRepository;

class ContractualisationType extends AbstractType
{
    private $constraints = [];

    /**
     * ContractualisationType constructor.
     *
     * @param Manager $manager
     * @param         $validator
     */
    public function __construct($manager, $validator)
    {
        $this->constraints = $manager->getConstraints($validator);
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation.
     *
     * @param FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param array                $options Data passée au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'required' => true,
                'label' => 'Fichier objet',
                'attr' => ['class' => $this->constraints['file']['class']],
            ])
            ->add('path', HiddenType::class)
            ->add('nomDocument', TextType::class, [
                'max_length' => $this->constraints['nomDocument']['maxlength'],
                'required' => true,
                'label' => 'Nom du document',
                'attr' => ['class' => $this->constraints['nomDocument']['class']],
            ])
            ->add('typeDocument', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => true,
                'label' => 'Type de document',
                'empty_value' => ' - ',
                'attr' => ['class' => $this->constraints['typeDocument']['class']],
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->leftJoin('ref.codes', 'codes')
                            ->where('codes.label = :etat')
                        ->setParameter('etat', 'DOCUMENT_CONTRACTUALISATION_TYPE')
                        ->orderBy('ref.order', 'ASC')
                    ;},
            ])
            ->add('dateRenouvellement', DateType::class, [
                'required' => false,
                'label' => 'Date de renouvellement',
                'widget' => 'single_text',
            ])
            ->add('archiver', CheckboxType::class, [
                'required' => false,
                'label' => 'Archiver le document ?',
                'attr' => ['class' => 'checkbox'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contractualisation::class,
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_user_contractualisation';
    }
}
