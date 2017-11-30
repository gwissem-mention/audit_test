<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type\Discussion;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DiscussionDomainType extends AbstractType
{
    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * DiscussionDomainType constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $builder
            ->add('domains', EntityType::class, [
                'class' => Domaine::class,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('domain')
                        ->join('domain.communautePratiqueGroupes', 'cdp_group')
                    ;
                },
                'choice_attr' => function (Domaine $domain) use ($user) {
                    if (!$user->getDomaines()->contains($domain)) {
                        return ['disabled' => 'disabled'];
                    }

                    return [];
                },
            ])
        ;

        $builder->get('domains')->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $formEvent) use ($user) {
                $objectDomains = $formEvent->getForm()->getData();
                $selectedData  = is_null($formEvent->getData()) ? [] : $formEvent->getData();

                $allowedDomains = $user->getDomaines()->toArray();

                $finalDomainList = [];

                // Get all object's domains the user doesn't have access to
                foreach ($objectDomains as $objectDomain) {
                    if (!in_array($objectDomain, $allowedDomains)) {
                        $finalDomainList[] = $objectDomain->getId();
                    }
                }

                // Adds user-selected domains
                foreach ($selectedData as $domainId) {
                    if (!empty($domainId) && !in_array($domainId, $finalDomainList)) {
                        $finalDomainList[] = $domainId;
                    }
                }

                $formEvent->setData($finalDomainList);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Discussion::class,
            'csrf_protection' => false,
        ]);
    }
}
