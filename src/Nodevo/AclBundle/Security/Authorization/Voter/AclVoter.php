<?php 
namespace Nodevo\AclBundle\Security\Authorization\Voter;

use Nodevo\AclBundle\Manager\AclManager;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AclVoter implements VoterInterface
{
    private $_aclManager;
    private $_requestStack;

    /**
     * On initialise le Voter avec un container (utilisé pour les services et l'appel du manager)
     *
     * @param AclManager    $aclManager     Manager des acls
     * @param RequestStack  $requestStack   RequestStack
     */
    public function __construct(AclManager $aclManager, RequestStack $requestStack)
    {
        $this->_aclManager = $aclManager;
        $this->_requestStack = $requestStack;
    }

    /**
     * @inheritdoc
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Test la ressource et retourne l'accès ( ACCESS_ABSTAIN | ACCESS_GRANTED | ACCESS_DENIED )
     *
     * @param TokenInterface $token      Token de sécurité (Symfony Token : get user)
     * @param Object         $object     Object
     * @param array          $attributes Les attributs
     *
     * @return ACCESS_ABSTAIN | ACCESS_GRANTED | ACCESS_DENIED
     */
    function vote(TokenInterface $token, $object, array $attributes)
    {
        //récupère la liste des rôles de l'user connecté
        if($token->getUser() != 'anon.') {
            //récupère l'url demandée
            $url = $requestStack->getCurrentRequest()->getRequestUri();

            //on vérifie que l'user connecté à accès à la route demandée
            return $this->_aclManager->checkAuthorization( $url , $token->getUser() );
        }else
            return VoterInterface::ACCESS_ABSTAIN;
    }
}