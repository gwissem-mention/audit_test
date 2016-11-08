<?php

namespace HopitalNumerique\PublicationBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuggestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => ['class' => 'validate[required,max[255]]'],
                'label' => 'Titre',
            ])
            ->add('creationDate', 'genemu_jquerydate', [
                'widget' => 'single_text',
                'label' => 'Date à laquelle la suggestion a été postée',
                'read_only' => true,
            ])
            ->add('domains', EntityType::class, [
                'class'       => Domaine::class,
                'multiple'    => true,
                'empty_value' => ' - ',
                'attr'        => [
                    'class' => 'select2 validate[required,minSize[3],maxSize[255]]',
                ],
                'label' => 'Domaine(s) associé(s)',
            ])
            ->add('state', EntityType::class, [
                'class'         => Reference::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('reference')
                        ->where("reference.code = 'ETAT_SUGGESTION'");
                },
                'label' => 'État',
                'choice_label' => 'libelle',
            ])
            ->add('synthesis', TextareaType::class, [
                'required' => false,
                'attr'     => ['class' => 'tinyMce'],
                'label' => 'Synthèse',
            ])
            ->add('summary', TextareaType::class, [
                'attr' => ['class' => 'tinyMce'],
                'label' => 'Résumé',
            ])
            ->add('link', null, [
                'required' => false,
                'label' => 'Lien web',
                'attr' => ['class' => 'validate[custom[url]]'],
            ])
            ->add('file', FileType::class, array(
                'required' => false,
                'label'    => 'ou fichier (max 10Mo)'
            ))
            ->add('path', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\PublicationBundle\Entity\Suggestion',
        ));
    }
}
