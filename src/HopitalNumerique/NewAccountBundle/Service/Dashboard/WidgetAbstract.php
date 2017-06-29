<?php

namespace HopitalNumerique\NewAccountBundle\Service\Dashboard;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class WidgetAbstract implements WidgetInterface
{
    /**
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * WidgetAbstract constructor.
     *
     * @param \Twig_Environment $twig
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     */
    public function __construct(\Twig_Environment $twig, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }
}
