<?php

namespace Nodevo\TexteDynamiqueBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Manager\UserManager;

class CodeType extends AbstractType
{
    private $_constraints = [];
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
            ->add('code', 'text', [
                'max_length' => 255,
                'required' => true,
                'label' => 'Code',
                'attr' => [
                        'class' => 'validate[required]',
                        'placeholder' => 'Fonctionnalité_page_identifiant',
                ],
            ])
            ->add('texte', 'textarea', [
                'required' => true,
                'label' => 'Texte à afficher',
                'attr' => ['rows' => 10, 'class' => 'validate[required] tinyMceCode'],
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
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Nodevo\TexteDynamiqueBundle\Entity\Code',
        ]);
    }

    public function getName()
    {
        return 'nodevo_textedynamique_code';
    }
}
