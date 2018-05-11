<?php

namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;
use Nodevo\ToolsBundle\Manager\Manager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form used to set user's informations required to ask intervention.
 */
class RequiredUserDataType extends AbstractType
{
    /**
     * @var array
     */
    private $constraints = [];

    /**
     * UserType constructor.
     *
     * @param Manager $manager
     * @param $validator
     */
    public function __construct(Manager $manager, $validator)
    {
        $this->constraints = $manager->getConstraints($validator);
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profileType', EntityType::class, [
                'class' => Reference::class,
                'query_builder' => function (ReferenceRepository $referenceRepository) {
                    return $referenceRepository->createQueryBuilder('ref')
                        ->join('ref.codes', 'codes')
                        ->where('codes.label = :code')
                        ->setParameter('code', 'CONTEXTE_METIER_INTERNAUTE')
                        ->orderBy('ref.order', 'ASC')
                    ;
                },
                'choice_label' => 'libelle',
                'label' => 'Profil',
                'required' => true,
                'empty_value' => ' - ',
            ])
            ->add('phoneNumber', TextType::class, [
                'max_length' => $this->constraints['phoneNumber']['maxlength'],
                'required' => true,
                'label' => 'Téléphone fixe',
                'attr' => [
                    'class' => $this->constraints['phoneNumber']['class'],
                    'data-mask' => $this->constraints['phoneNumber']['mask'],
                ],
            ])
            ->add('cellPhoneNumber', TextType::class, [
                'max_length' => $this->constraints['cellPhoneNumber']['maxlength'],
                'required' => true,
                'label' => 'Téléphone portable',
                'attr' => [
                    'class' => $this->constraints['cellPhoneNumber']['class'],
                    'data-mask' => $this->constraints['cellPhoneNumber']['mask'],
                ],
            ])
        ;
    }
}
