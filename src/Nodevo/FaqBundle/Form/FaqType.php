<?php

namespace Nodevo\FaqBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Doctrine\ORM\EntityRepository;

class FaqType extends AbstractType
{
    private $_userManager;

    public function __construct($manager, $validator, UserManager $userManager)
    {
        $this->_constraints = $manager->getConstraints($validator);
        $this->_userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->_userManager->getUserConnected();

        $builder
            ->add('question', 'text', [
                'required' => true,
                'label' => 'Question',
            ])
            ->add('reponse', 'textarea', [
                'required' => true,
                'label' => 'Réponse',
                'label_attr' => ['class' => 'col-md-12'],
                'attr' => ['class' => 'tinyMce'],
            ])
            ->add('order', 'integer', [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('categorie', 'genemu_jqueryselect2_entity', [
                'class' => 'NodevoFaqBundle:Categorie',
                'property' => 'name',
                'required' => true,
                'label' => 'Catégorie',
                'empty_value' => ' - ',
                'attr' => ['placeholder' => 'Selectionnez la catégorie correspondante'],
            ])
            ->add('domaines', 'entity', [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property' => 'nom',
                'required' => false,
                'multiple' => true,
                'label' => 'Domaine(s) associé(s)',
                'empty_value' => ' - ',
                'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                },
            ])
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Nodevo\FaqBundle\Entity\Faq',
        ]);
    }

    public function getName()
    {
        return 'nodevo_faq_faq';
    }
}
