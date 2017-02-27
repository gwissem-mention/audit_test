<?php

namespace Nodevo\RoleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

class RoleType extends AbstractType
{
    private $_constraints = [];
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    public function __construct($manager, $validator, ReferenceManager $referenceManager)
    {
        $this->_constraints = $manager->getConstraints($validator);
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'max_length' => $this->_constraints['name']['maxlength'],
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => $this->_constraints['name']['class']],
            ]);

        //On peut uniquement modifier l'état des groupes NON initiaux
        if (!$options['data']->getInitial()) {
            $builder
                ->add('etat', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('ETAT'),
                    'property' => 'libelle',
                    'required' => true,
                    'label' => 'Etat',
                ]);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Nodevo\RoleBundle\Entity\Role',
        ]);
    }

    public function getName()
    {
        return 'nodevo_role_role';
    }
}
