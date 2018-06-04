<?php

namespace HopitalNumerique\ModuleBundle\Service\Widget;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use HopitalNumerique\ModuleBundle\Service\Widget\DataProvider\SessionProvider;
use HopitalNumerique\ModuleBundle\Service\Widget\DataProvider\RegistrationProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SessionWidget
 */
class SessionWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var RegistrationProvider
     */
    protected $registrationProvider;

    /**
     * @var SessionProvider
     */
    protected $sessionProvider;

    /**
     * @var CurrentDomaine $currentDomain
     */
    protected $currentDomain;

    /**
     * SessionWidget constructor.
     *
     * @param \Twig_Environment $twig
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param RegistrationProvider $registrationProvider
     * @param SessionProvider $sessionProvider
     * @param CurrentDomaine $currentDomain
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RegistrationProvider $registrationProvider,
        SessionProvider $sessionProvider,
        CurrentDomaine $currentDomain
    ) {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->registrationProvider = $registrationProvider;
        $this->sessionProvider = $sessionProvider;
        $this->currentDomain = $currentDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $data = [
            'registrations' => $this->registrationProvider->getRegistrationData($this->currentDomain->get()),
            'sessions' => $this->sessionProvider->getSessionData($this->currentDomain->get()),
        ];

        if (empty($data['registrations']) && empty($data['sessions'])) {
            return null;
        }

        $html = $this->twig->render('HopitalNumeriqueModuleBundle:widget:session.html.twig', [
            'data' => $data,
        ]);

        $title = $this->translator->trans('session.title', [], 'widget');

        return new Widget('sessions', $title, $html);
    }
}
