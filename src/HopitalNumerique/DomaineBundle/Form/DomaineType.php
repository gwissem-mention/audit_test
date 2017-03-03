<?php

namespace HopitalNumerique\DomaineBundle\Form;

use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class DomaineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $domaine = $builder->getData();

        $builder
            ->add('nom', 'text', [
                'max_length' => 255,
                'required' => true,
                'label' => 'Nom du domaine',
                'attr' => ['class' => 'validate[required,max[255]]'],
            ])
            ->add('file', 'file', [
                'required' => false,
                'label' => 'Logo du domaine',
            ])
            ->add('description', 'textarea', [
                'required' => false,
                'label' => 'Description',
                'attr' => ['rows' => 3],
            ])
            ->add('googleAnalytics', 'textarea', [
                'required' => false,
                'label' => 'Google Analytics',
                'attr' => ['rows' => 3],
            ])
            ->add('path', 'hidden')
            ->add('url', 'text', [
                'max_length' => 255,
                'required' => true,
                'label' => 'URL du domaine',
                'attr' => ['class' => 'validate[required,max[255],custom[url]]'],
            ])
            ->add('adresseMailContact', 'text', [
                'max_length' => 255,
                'required' => true,
                'label' => 'Adresse mail du contact',
                'attr' => ['class' => 'validate[required,max[255],custom[email]]'],
            ])
            ->add('template', 'genemu_jqueryselect2_entity', [
                'class' => 'HopitalNumeriqueDomaineBundle:Template',
                'property' => 'nom',
                'multiple' => false,
                'required' => true,
                'label' => 'Template',
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('homepage', 'textarea', [
                'required' => false,
                'label' => 'Texte affiché sur la homepage',
                'attr' => ['rows' => 2, 'class' => 'tinyMceDomaine'],
            ])
            ->add('urlTitre', 'text', [
                'required' => false,
                'label' => 'Lien du titre',
            ])
        ;
        if (null !== $domaine && null !== $domaine->getId()) {
            $builder
                ->add('referenceRoot', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'label' => 'Référence root',
                    'required' => false,
                ])
                ->add('communautePratiqueArticle', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumerique\ObjetBundle\Entity\Objet',
                    'property' => 'titre',
                    'multiple' => false,
                    'required' => false,
                    'label' => 'Article de la communauté de pratique',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) use ($domaine) {
                        $qb = $er->createQueryBuilder('obj');
                        $qb
                            ->addSelect('types')
                            ->join('obj.types', 'types')
                            ->join('obj.domaines', 'domaine', Join::WITH, 'domaine.id = :domaine_id')
                            ->groupBy('obj.id')
                            ->orderBy('obj.titre')
                            ->setParameters([
                                'domaine_id' => $domaine->getId(),
                            ])
                        ;

                        return $qb;
                    },
                ])
                ->add('communautePratiqueForumCategories', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumerique\ForumBundle\Entity\Category',
                    'group_by' => 'forum',
                    'property' => 'name',
                    'multiple' => true,
                    'required' => false,
                    'label' => 'Catégorie(s) de forum pour la communauté de pratique',
                    'empty_value' => ' - ',
                    'attr' => [
                        'class' => 'select2',
                    ],
                ])
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\DomaineBundle\Entity\Domaine',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_domaine_domaine';
    }
}
