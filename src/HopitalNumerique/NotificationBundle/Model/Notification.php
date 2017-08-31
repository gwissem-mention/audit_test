<?php

namespace HopitalNumerique\NotificationBundle\Model;

/**
 * Class Notification.
 */
class Notification
{
    /**
     * @var string $uid Notification unique identifier
     */
    protected $uid;

    /**
     * @var string $originProviderCode Code of notification provider that created this object.
     */
    protected $originProviderCode;

    /**
     * @var string $title Notification title
     */
    protected $title;

    /**
     * @var string $detail Notification detail
     */
    protected $detail;

    /**
     * @var array $data Additional notification data
     */
    protected $data;

    /**
     * @var \DateTime $dateTime Date and time of notification
     */
    protected $dateTime;

    /**
     * Notification constructor.
     *
     * @param string|array $uid                Notification unique id (string, or array to be stringified with md5)
     * @param string       $originProviderCode Code of notification provider that created this notification
     * @param string       $title              Notification title
     * @param string|null  $detail             Notification detail
     * @param array        $data               Notification additional data
     */
    public function __construct($uid, $originProviderCode, $title = null, $detail = null, array $data = [])
    {
        $this->uid = is_array($uid) ? md5(implode($uid)) : $uid;
        $this->originProviderCode = $originProviderCode;
        $this->title = $title;
        $this->detail = $detail;
        $this->data = $data;

        $this->dateTime = new \DateTime();
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getUid();
    }

    /**
     * @return string
     */
    public function getProviderCode()
    {
        return $this->originProviderCode;
    }

    /**
     * @param string $title Notification title
     *
     * @return Notification
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $detail Notification detail
     *
     * @return Notification
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param array $data Notification data
     *
     * @return Notification
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return Notification
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param string|null $key Searched key.
     *
     * @return array|mixed
     */
    public function getData($key = null)
    {
        if (null === $key) {
            return $this->data;
        } elseif (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            return null;
        }
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
