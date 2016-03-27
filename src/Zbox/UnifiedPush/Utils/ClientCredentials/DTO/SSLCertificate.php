<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Utils\ClientCredentials\DTO;

use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsInterface;

/**
 * Class Credentials
 * @package Zbox\UnifiedPush\Utils\ClientCredentials
 */
class SSLCertificate implements CredentialsInterface
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
     * @param string $certificate
     * @return $this
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * @param string $certificatePassPhrase
     * @return $this
     */
    public function setCertificatePassPhrase($certificatePassPhrase)
    {
        $this->certificatePassPhrase = $certificatePassPhrase;

        return $this;
    }
}
