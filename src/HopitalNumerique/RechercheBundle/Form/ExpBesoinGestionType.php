<?php

namespace HopitalNumerique\RechercheBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class ExpBesoinGestionType
 */
class ExpBesoinGestionType extends AbstractType
{
    private $userManager;
    private $constraints;

    /**
     * ExpBesoinGestionType constructor.
     *
     * @param             $manager
     * @param             $validator
     * @param UserManager $userManager
     */
    public function __construct($manager, $validator, UserManager $userManager)
    {
        $this->constraints = $manager->getConstraints($validator);
        $this->userManager  = $userManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->userManager->getUserConnected();

        $builder
            ->add('nom', TextType::class, [
                'max_length' => 255,
                'required' => true,
                'label' => 'Nom de la recherche aidée',
            ])
            ->add('domaines', EntityType::class, [
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

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_recherche_expbesoingestion';
    }
}
