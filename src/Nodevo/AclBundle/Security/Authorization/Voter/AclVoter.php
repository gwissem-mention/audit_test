<?php 
namespace Nodevo\AclBundle\Security\Authorization\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AclVoter implements VoterInterface
{
    /**
     * On initialise le Voter avec un container (utilisé pour les services et l'appel du manager)
     *
     * @param ContainerInterface $container Container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
            $url = $this->container->get('request')->getRequestUri();

            //on vérifie que l'user connecté à accès à la route demandée
            return $this->container->get('nodevo_acl.manager.acl')->checkAuthorization( $url , $token->getUser() );
        }else
            return VoterInterface::ACCESS_ABSTAIN;
    }
}