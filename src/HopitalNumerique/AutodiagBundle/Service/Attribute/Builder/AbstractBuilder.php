<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * Class AbstractBuilder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
abstract class AbstractBuilder implements AttributeBuilderInterface
{
    /** @var \Twig_Environment */
    protected $twig;

    /** @var CsrfTokenManager */
    protected $tokenManager;

    public function setTwigRenderer(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function setCsrfTokenManager($tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function getFormHtml(Value $entryValue, $viewData = [])
    {
        $value = $this->transform($entryValue->getValue());
        if (null === $value && null !== $entryValue->getId()) {
            $value = -1;
        }

        return $this->twig->render(
            sprintf('HopitalNumeriqueAutodiagBundle:AutodiagEntry:Attribute/%s.html.twig', $this->getTemplateName()),
            [
                'attribute' => $entryValue->getAttribute(),
                'entry' => $entryValue->getEntry(),
                'value' => $value,
                'comment' => $entryValue->getComment(),
                'viewData' => $viewData,
                'token' => $this->tokenManager->getToken('entry_value_intention')->getValue(),
            ]
        );
    }
}
