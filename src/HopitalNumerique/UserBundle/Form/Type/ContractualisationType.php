<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use HopitalNumerique\UserBundle\Entity\Contractualisation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractualisationType extends AbstractType
{
    private $_constraints = [];

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints($validator);
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
                'attr' => ['class' => $this->_constraints['file']['class']],
            ])
            ->add('path', HiddenType::class)
            ->add('nomDocument', 'text', [
                'max_length' => $this->_constraints['nomDocument']['maxlength'],
                'required' => true,
                'label' => 'Nom du document',
                'attr' => ['class' => $this->_constraints['nomDocument']['class']],
            ])
            ->add('typeDocument', EntityType::class, [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Type de document',
                    'empty_value' => ' - ',
                    'attr' => ['class' => $this->_constraints['typeDocument']['class']],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->leftJoin('ref.codes', 'codes')
                            ->where('codes.label = :etat')
                            ->setParameter('etat', 'DOCUMENT_CONTRACTUALISATION_TYPE')
                            ->orderBy('ref.order', 'ASC')
                        ;
                    },
            ])
            ->add('dateRenouvellement', 'genemu_jquerydate', [
                'required' => false,
                'label' => 'Date de renouvellement',
                'widget' => 'single_text',
            ])
            ->add('archiver', CheckboxType::class, [
                'required' => false,
                'label' => 'Archiver le document ?',
                'attr' => ['class' => 'checkbox'],//array('class' => $this->_constraints['archiver']['class'] )
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
