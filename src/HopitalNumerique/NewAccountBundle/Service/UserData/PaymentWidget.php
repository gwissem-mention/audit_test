<?php

namespace HopitalNumerique\NewAccountBundle\Service\UserData;

use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\ModuleBundle\Manager\InscriptionManager;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Security\InformationsAccessVoter;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetInterface;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use HopitalNumerique\PaiementBundle\Manager\FactureManager;
use HopitalNumerique\PaiementBundle\Manager\RemboursementManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PaymentWidget
 */
class PaymentWidget implements WidgetInterface, DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var RemboursementManager
     */
    protected $remboursementManager;

    /**
     * @var InterventionDemandeManager
     */
    protected $interventionDemandeManager;

    /**
     * @var InscriptionManager
     */
    protected $inscriptionManager;

    /**
     * @var FactureManager
     */
    protected $factureManager;

    /**
     * ContractWidget constructor.
     *
     * @param \Twig_Environment $twig
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TranslatorInterface $translator
     * @param RemboursementManager $remboursementManager
     * @param InterventionDemandeManager $interventionDemandeManager
     * @param InscriptionManager $inscriptionManager
     *
     * @param FactureManager $factureManager
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator,
        RemboursementManager $remboursementManager,
        InterventionDemandeManager $interventionDemandeManager,
        InscriptionManager $inscriptionManager,
        FactureManager $factureManager
    ) {
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
        $this->remboursementManager = $remboursementManager;
        $this->interventionDemandeManager = $interventionDemandeManager;
        $this->inscriptionManager = $inscriptionManager;
        $this->factureManager = $factureManager;
    }

    /**
     * @return Widget|null
     */
    public function getWidget()
    {
        if (!$this->authorizationChecker->isGranted(InformationsAccessVoter::ACCESS_PAYMENTS_ACTIVITY)) {
            return null;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $interventions = $this->interventionDemandeManager->getForFactures($user);
        $formations = $this->inscriptionManager->getForFactures($user);

        return new Widget(
            'payment',
            $this->translator->trans('account.profile.payments'),
            $this->twig->render('@NewAccount/profile/tabs/payments.html.twig', [
                'datas' => $this->remboursementManager->calculPrice($interventions, $formations),
                'factures' => $this->factureManager->getFacturesOrdered($user),
                'canGenererFacture' => $this->factureManager->canGenererFacture($interventions),
            ])
        );
    }
}
