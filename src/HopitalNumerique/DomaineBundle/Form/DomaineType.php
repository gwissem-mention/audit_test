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
            ->add('nom', 'text', array(
                'max_length' => 255,
                'required'   => true,
                'label'      => 'Nom du domaine',
                'attr'       => array('class' => 'validate[required,max[255]]')
            ))
            ->add('file', 'file', array(
                'required' => false,
                'label'    => 'Logo du domaine'
            ))
            ->add('description', 'textarea', array(
                'required'   => false,
                'label'      => 'Description',
                'attr'       => array('rows' => 3)
            ))
            ->add('googleAnalytics', 'textarea', array(
                'required'   => false,
                'label'      => 'Google Analytics',
                'attr'       => array('rows' => 3)
            ))
            ->add('path', 'hidden')
            ->add('url', 'text', array(
                'max_length' => 255,
                'required'   => true,
                'label'      => 'URL du domaine',
                'attr'       => array('class' => 'validate[required,max[255],custom[url]]')
            ))
            ->add('adresseMailContact', 'text', array(
                'max_length' => 255,
                'required'   => true,
                'label'      => 'Adresse mail du contact',
                'attr'       => array('class' => 'validate[required,max[255],custom[email]]')
            ))
            ->add('template', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueDomaineBundle:Template',
                    'property'      => 'nom',
                    'multiple'      => false,
                    'required'      => true,
                    'label'         => 'Template',
                    'empty_value'   => ' - ',
                    'attr'       => array('class' => 'validate[required]')
            ))
            ->add('homepage', 'textarea', array(
                'required'   => false,
                'label'      => 'Texte affiché sur la homepage',
                'attr'       => array('rows' => 2, 'class' => 'tinyMceDomaine')
            ))
            ->add('urlTitre', 'text', array(
                    'required'   => false,
                    'label'      => 'Lien du titre',
            ))
        ;
        if (null !== $domaine && null !== $domaine->getId()) {
            $builder
                ->add('referenceRoot', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'label' => 'Référence root',
                    'required' => false
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
                                'domaine_id' => $domaine->getId()
                            ])
                        ;
                        return $qb;
                    }
                ])
                ->add('communautePratiqueForumCategory', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumerique\ForumBundle\Entity\Category',
                    'group_by' => 'forum',
                    'property' => 'name',
                    'multiple' => false,
                    'required' => false,
                    'label' => 'Catégorie de forum pour la communauté de pratique',
                    'empty_value' => ' - ',
                ])
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\DomaineBundle\Entity\Domaine'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_domaine_domaine';
    }
}
