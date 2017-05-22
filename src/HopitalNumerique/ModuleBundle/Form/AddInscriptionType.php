<?php

namespace HopitalNumerique\ModuleBundle\Form;

use HopitalNumerique\ModuleBundle\Entity\Inscription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class AddInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roleNames = (isset($options['label_attr']['roleNames']) && !is_null($options['label_attr']['roleNames']))
            ? $options['label_attr']['roleNames'] : [];

        $builder
            ->add('commentaire', TextareaType::class, [
                    'required' => false,
                    'label' => 'Informations complémentaires',
                    'attr' => [
                        'rows' => 8,
                    ],
            ])
            ->add('user', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueUserBundle:User',
                    'property' => 'appellation',
                    'required' => true,
                    'label' => 'Utilisateur',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) use ($roleNames) {
                        return $er->getUsersByRole($roleNames);
                    },
                    'attr' => [
                        'class' => 'validate[required]'
                    ],
            ])
            ->add('etatInscription', EntityType::class, [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Inscription',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->leftJoin('ref.codes', 'codes')
                            ->where('codes.label = :etat')
                            ->setParameter('etat', 'STATUT_FORMATION')
                            ->orderBy('ref.order', 'ASC')
                        ;
                    },
                    'attr' => [
                        'class' => 'validate[required]'
                    ],
            ])
            ->add('etatParticipation', EntityType::class, [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Participation',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->leftJoin('ref.codes', 'codes')
                            ->where('codes.label = :etat')
                            ->setParameter('etat', 'STATUT_PARTICIPATION')
                            ->orderBy('ref.order', 'ASC')
                        ;
                    },
                    'attr' => [
                        'class' => 'validate[required]'
                    ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_module_addinscription';
    }
}
