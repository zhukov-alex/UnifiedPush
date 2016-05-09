<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsInterface;
use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsMapper;
use Zbox\UnifiedPush\Exception\DomainException;
use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\AuthToken;
use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\NullCredentials;
use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\SSLCertificate;

class ServiceCredentialsFactory
{
    /**
     * @var CredentialsMapper
     */
    protected $credentialsMapper;

    /**
     * @var CredentialsInterface[]
     */
    protected $serviceCredentials;

    /**
     * @param CredentialsMapper $credentialsMapper
     */
    public function __construct(CredentialsMapper $credentialsMapper)
    {
        $this->credentialsMapper = $credentialsMapper;
    }

    /**
     * @param string $serviceName
     * @param array $credentials
     * @return $this
     */
    public function addCredentialsForService($serviceName, $credentials)
    {
        $credentialsDTO =
            $this
                ->credentialsMapper
                ->mapCredentials(
                    $this->getCredentialsDTOByServiceName($serviceName),
                    $credentials
                );

        $this->serviceCredentials[$serviceName] = $credentialsDTO;

        return $this;
    }

    /**
     * Returns the list of names of notification services
     *
     * @return array
     */
    public function getInitializedServices()
    {
        return array_keys($this->serviceCredentials);
    }

    /**
     * Returns credentials for notification service
     *
     * @param string $serviceName
     * @throws DomainException
     * @return CredentialsInterface
     */
    public function getCredentialsByService($serviceName)
    {
        if (!in_array($serviceName, $this->getInitializedServices())) {
            throw new DomainException(
                sprintf("Credentials for service '%s' was not initialized", $serviceName)
            );
        }

        return $this->serviceCredentials[$serviceName];
    }

    /**
     * @param string $serviceName
     * @return CredentialsInterface
     */
    private function getCredentialsDTOByServiceName($serviceName)
    {
        $credentialsType = NotificationServices::getCredentialsTypeByService($serviceName);

        switch ($credentialsType) {
            case NotificationServices::CREDENTIALS_CERTIFICATE:
                return new SSLCertificate();

            case NotificationServices::CREDENTIALS_AUTH_TOKEN:
                return new AuthToken();

            case NotificationServices::CREDENTIALS_NULL:
                return new NullCredentials();

            default:
                throw new DomainException(sprintf("Unsupported credentials type '%s'", $credentialsType));
        }
    }
}
