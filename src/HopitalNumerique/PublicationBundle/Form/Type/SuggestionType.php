<?php

namespace HopitalNumerique\PublicationBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SuggestionType extends AbstractType
{
    /** @var TokenStorage $tokenStorage */
    private $tokenStorage;

    /** @var EntityManager $entityManager */
    private $entityManager;

    /**
     * SuggestionType constructor.
     *
     * @param TokenStorage  $tokenStorage
     * @param EntityManager $entityManager
     */
    public function __construct(TokenStorage $tokenStorage, EntityManager $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => ['class' => 'validate[required,max[255]]'],
                'label' => 'Titre',
            ])
            ->add('creationDate', 'genemu_jquerydate', [
                'widget' => 'single_text',
                'label' => 'Date à laquelle la suggestion a été postée',
                'read_only' => true,
            ])
            ->add('domains', EntityType::class, [
                'class' => Domaine::class,
                'multiple' => true,
                'empty_value' => ' - ',
                'attr' => [
                    'class' => 'select2 validate[required,minSize[3],maxSize[255]]',
                ],
                'label' => 'Domaine(s) associé(s)',
            ])
            ->add('state', EntityType::class, [
                'class' => Reference::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('reference')
                        ->join('reference.codes', 'referenceCode', Expr\Join::WITH, "referenceCode.label = 'ETAT_SUGGESTION'")
                    ;
                },
                'label' => 'État',
                'choice_label' => 'libelle',
            ])
            ->add('synthesis', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'tinyMce'],
                'label' => 'Synthèse',
            ])
            ->add('summary', TextareaType::class, [
                'attr' => ['class' => 'tinyMce'],
                'label' => 'Résumé',
            ])
            ->add('link', null, [
                'required' => false,
                'label' => 'Lien web',
                'attr' => ['class' => 'validate[custom[url]]'],
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'label' => 'ou fichier (max 10Mo)',
            ])
            ->add('path', HiddenType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        // Si on change l'état de la suggestion, on sauvegarde la date et l'auteur du changement
        if ($event->getForm()->getData()->getId() != null) {
            /** @var Suggestion $suggestion */
            $suggestion = $event->getForm()->getData();
            if (isset($event->getData()['state']) && $event->getData()['state'] != $suggestion->getState()->getId()) {
                $suggestion->setStateChangeAuthor($this->tokenStorage->getToken()->getUser());
                $suggestion->setStateChangeDate(new \DateTime());

                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\PublicationBundle\Entity\Suggestion',
        ]);
    }
}
