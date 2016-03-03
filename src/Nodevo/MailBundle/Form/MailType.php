<?php

namespace Nodevo\MailBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MailType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objet', 'text', array(
                'max_length' => $this->_constraints['objet']['maxlength'],
                'required'   => true, 
                'label'      => 'Objet',
                'attr'       => array('class' => $this->_constraints['objet']['class'] )
            ))
            ->add('description', 'text', array(
                'max_length' => $this->_constraints['description']['maxlength'],
                'required'   => true, 
                'label'      => 'Description',
                'attr'       => array('class' => $this->_constraints['description']['class'] )
            ))
            ->add('expediteurMail', 'text', array(
                'max_length' => $this->_constraints['expediteurMail']['maxlength'],
                'required'   => true, 
                'label'      => 'E-mail de l\'expéditeur',
                'attr'       => array('class' => $this->_constraints['expediteurMail']['class'] )
            ))
            ->add('expediteurName', 'text', array(
                'max_length' => $this->_constraints['expediteurName']['maxlength'],
                'required'   => true, 
                'label'      => 'Nom expéditeur',
                'attr'       => array('class' => $this->_constraints['expediteurName']['class'] )
            ))
            ->add('body', 'textarea', array(
                'required'   => true, 
                'label'      => 'Corps du mail',
                'attr'       => array('class' => $this->_constraints['body']['class'], 'rows' => 10 )
            ))
            ->add('notificationRegionReferent', 'checkbox', [
                'label' => 'Notifier le référent de la région du destinataire',
                'required' => false
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nodevo\MailBundle\Entity\Mail'
        ));
    }

    public function getName()
    {
        return 'nodevo_mail_mail';
    }
}
