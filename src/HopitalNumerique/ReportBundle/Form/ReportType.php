<?php

namespace HopitalNumerique\ReportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportType extends AbstractType
{
    private $_constraints = [];

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints($validator);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', 'text', [
                        'required' => true,
                        'label' => 'Url',
                        'attr' => [
                            'class' => $this->_constraints['observations']['class'],
                            'readonly' => 'readonly',
                            ],
            ])
            ->add('observations', 'textarea', [
                        'required' => true,
                        'label' => 'Observations',
                        'attr' => [
                            'class' => $this->_constraints['observations']['class'],
                            'rows' => 10,
                            ],
            ])
            ->add('userAgent', 'hidden', [])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\ReportBundle\Entity\Report',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_reportbundle_report';
    }
}
