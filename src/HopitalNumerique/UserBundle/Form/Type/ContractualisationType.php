<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

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
            ->add('file', 'file', [
                'required' => true,
                'label' => 'Fichier objet',
                'attr' => ['class' => $this->_constraints['file']['class']],
            ])
            ->add('path', 'hidden')
            ->add('nomDocument', 'text', [
                'max_length' => $this->_constraints['nomDocument']['maxlength'],
                'required' => true,
                'label' => 'Nom du document',
                'attr' => ['class' => $this->_constraints['nomDocument']['class']],
            ])
            ->add('typeDocument', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Type de document',
                    'empty_value' => ' - ',
                    'attr' => ['class' => $this->_constraints['typeDocument']['class']],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'DOCUMENT_CONTRACTUALISATION_TYPE')
                        ->orderBy('ref.order', 'ASC');
                    },
            ])
            ->add('dateRenouvellement', 'genemu_jquerydate', [
                'required' => false,
                'label' => 'Date de renouvellement',
                'widget' => 'single_text',
            ])
            ->add('archiver', 'checkbox', [
                'required' => false,
                'label' => 'Archiver le document ?',
                'attr' => ['class' => 'checkbox'],//array('class' => $this->_constraints['archiver']['class'] )
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\UserBundle\Entity\Contractualisation',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_user_contractualisation';
    }
}
