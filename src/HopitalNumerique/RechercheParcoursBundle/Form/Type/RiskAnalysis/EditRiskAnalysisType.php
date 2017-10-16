<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form\Type\RiskAnalysis;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;

class EditRiskAnalysisType extends AbstractType
{
    /**
     * @var ObjectIdentityRepository $objectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * EditRiskAnalysisType constructor.
     *
     * @param ObjectIdentityRepository $objectIdentityRepository
     */
    public function __construct(ObjectIdentityRepository $objectIdentityRepository)
    {
        $this->objectIdentityRepository = $objectIdentityRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('probability', IntegerType::class)
            ->add('impact', IntegerType::class)
            ->add('initialSkillsRate', IntegerType::class)
            ->add('currentSkillsRate', IntegerType::class)
            ->add('comment', TextareaType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $objects = $this->objectIdentityRepository->getRelatedByObjects(
                ObjectIdentity::createFromDomainObject($event->getData()->getRisk()),
                Objet::class
            );
            $choices = [];
            foreach ($objects as $object) {
                $choices[$object->getObject()->getObjectIdentityTitle()] = $object->getObject();
            }

            $event->getForm()->add('excludedObjects', EntityType::class, [
                'class'    => Objet::class,
                'multiple' => true,
                'choices'  => $choices,
            ])
            ;
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => RiskAnalysis::class,
            'csrf_protection' => false,
            'required'        => false,
        ]);
    }
}
