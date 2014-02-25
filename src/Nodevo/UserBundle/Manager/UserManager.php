<?php

namespace Nodevo\UserBundle\Manager;

use Doctrine\ORM\EntityManager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
#use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager extends BaseManager
{
   
    

    // public function __construct( EntityManager $em, EncoderFactory $encoderFactory, $userClass )
    // {
    //     $this->_em             = $em;
    //     $this->_class          = $userClass;
    //     $this->_encoderFactory = $encoderFactory;
    //     $this->_repository     = $this->_em->getRepository( $this->_class );
    // }

    // /**
    //  * Find a user by his username
    //  * 
    //  * @param  string $username Username to find
    //  * 
    //  * @return UserInterface User found
    //  */
    // public function findUserByUsername( $username )
    // {
    //     $user = $this->_repository->findOneByUsername( $username );
    //     return $user;
    // }

    // /**
    //  * Update user password, if plainPassword isn't empty, encode it and set it to password field
    //  * 
    //  * @param  UserInterface $user User to update password
    //  */
    // public function updatePassword( $user )
    // {
    //     if (0 !== strlen($password = $user->getPlainPassword())) {
    //         $encoder = $this->getEncoder($user);
    //         $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
    //         $user->eraseCredentials();
    //     }
    // }

    // *
    //  * Update user
    //  * 
    //  * @param  UserInterface $user User to update
     
    // public function updateUser( $user )
    // {
    //     $this->updatePassword( $user );
    //     $this->_em->persist( $user );
    //     $this->_em->flush();
    // }

    // /**
    //  * Create user
    //  * 
    //  * @param  string $username
    //  * @param  string $password
    //  * @param  string $email
    //  * 
    //  * @return UserInterface Created user
    //  */
    // public function createUser( $username, $password, $email )
    // {
    //     $user = parent::create();
    //     $encoder = $this->_encoderFactory->getEncoder( $user );

    //     $user->setUsername( $username );
    //     $user->setPassword( $encoder->encodePassword($password, $user->getSalt()) );
    //     $user->setEmail( $email );

    //     $this->save( $user );

    //     return $user;
    // }
   
    // /**
    //  * Update last login date
    //  * 
    //  * @param  UserInterface $user User to be updated
    //  */
    // public function updateLastLogin( UserInterface $user )
    // {
    //     $user->setLastLogin( new \DateTime() );
    //     $this->save( $user );
    // }

    // /**
    //  * Get password encoder
    //  * 
    //  * @param  UserInterface $user [description]
    //  * 
    //  * @return mixed Encoder
    //  */
    // private function getEncoder( UserInterface $user ) {
    //     return $this->_encoderFactory->getEncoder( $user );
    // }
}