<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\MPNS;

use Zbox\UnifiedPush\NotificationService\CredentialsInterface;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class Credentials
 * @package Zbox\UnifiedPush\NotificationService\MPNS
 */
class Credentials implements CredentialsInterface
{
    /**
     * Unauthenticated web services are throttled at a rate of 500
     * push notifications per subscription, per day
     *
     * @var bool
     */
    private $isAuthenticated;

    /**
     * Provider certificate
     *
     * @var string
     */
    private $certificate;

    /**
     * Certificate pass phrase
     *
     * @var string
     */
    private $certificatePassPhrase;

    /**
     * @param array $credentials
     */
    public function __construct($credentials)
    {
        if (empty($credentials)) {
            $this->setAuthenticated(false);
            return $this;
        }

        $object = (object) $credentials;
        $this
            ->setCertificate($object)
            ->setCertificatePassPhrase($object)
            ->setAuthenticated(true)
        ;
        return $this;
    }

    /**
     * @param bool $isAuthenticated
     * @return $this
     */
    public function setAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return string
     */
    public function getCertificatePassPhrase()
    {
        return $this->certificatePassPhrase;
    }

    /**
     * @param \stdClass $credentials
     * @return $this
     */
    public function setCertificate($credentials)
    {
        if (empty($credentials->certificate) || !file_exists($credentials->certificate)) {
            throw new InvalidArgumentException("SSL/TSL Certificate required");
        }

        $this->certificate = $credentials->certificate;

        return $this;
    }

    /**
     * @param \stdClass $credentials
     * @return $this
     */
    public function setCertificatePassPhrase($credentials)
    {
        if (empty($credentials->certificatePassPhrase)) {
            throw new InvalidArgumentException("SSL/TSL Certificate passphrase required");
        }

        $this->certificatePassPhrase = $credentials->certificatePassPhrase;

        return $this;
    }
}
