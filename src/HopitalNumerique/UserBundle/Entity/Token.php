<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Token
 * @ORM\Table("core_user_token")
 * @ORM\Entity(repositoryClass="HopitalNumerique\UserBundle\Repository\TokenRepository")
 */
class Token
{
    const TOKEN_MAX_LENGTH = 76;

    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="token", type="string", length=76)
     */
    protected $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime")
     */
    protected $expiresAt;

    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string")
     */
    protected $sessionId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="usr_id")
     */
    protected $user;

    public function __construct($sessionId, User $user)
    {
        $this->token = $this->generateToken();
        $this->createdAt = new \DateTimeImmutable();
        $this->sessionId = $sessionId;
        $this->user = $user;

        $this->setLifetime(ini_get('session.cookie_lifetime'));
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime $expiresAt
     *
     * @return Token
     */
    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setLifetime($lifetime)
    {
        $this->expiresAt = $this->createdAt->add(new \DateInterval("PT${lifetime}S"));
    }

    protected function generateToken()
    {
        return bin2hex(random_bytes(floor(self::TOKEN_MAX_LENGTH / 2)));
    }
}
