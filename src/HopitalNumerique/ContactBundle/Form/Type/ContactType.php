<?php

namespace HopitalNumerique\ContactBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Nodevo\ContactBundle\Entity\Contact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nodevo\ContactBundle\Form\Type\ContactType as NodevoContactType;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ContactType extends NodevoContactType
{
    private $_constraints = [];

    public function __construct($manager, $validator, $securityContext)
    {
        parent::__construct($manager, $validator, $securityContext);

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
        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopital_numerique_contact_contact';
    }
}
