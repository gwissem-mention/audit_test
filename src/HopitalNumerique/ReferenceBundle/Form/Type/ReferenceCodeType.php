<?php

namespace HopitalNumerique\ReferenceBundle\Form\Type;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\TextType;
use HopitalNumerique\ReferenceBundle\Entity\ReferenceCode;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Nodevo\ToolsBundle\Tools\Systeme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReferenceCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReferenceCode::class,
        ]);
    }
}
