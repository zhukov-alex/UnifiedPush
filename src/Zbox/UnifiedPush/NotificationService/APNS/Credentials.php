<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\APNS;

use Zbox\UnifiedPush\NotificationService\CredentialsInterface;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class Credentials
 * @package Zbox\UnifiedPush\NotificationService\APNS
 */
class Credentials implements CredentialsInterface
{
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
        $object = (object) $credentials;
        $this
            ->setCertificate($object)
            ->setCertificatePassPhrase($object)
        ;
        return $this;
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
