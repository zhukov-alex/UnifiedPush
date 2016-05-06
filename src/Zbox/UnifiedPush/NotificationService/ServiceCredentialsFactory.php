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
use Zbox\UnifiedPush\Exception\InvalidArgumentException;
use Zbox\UnifiedPush\Exception\RuntimeException;
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
     * @var string
     */
    protected $credentialsPath;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param CredentialsMapper $credentialsMapper
     */
    public function __construct(CredentialsMapper $credentialsMapper)
    {
        $this->credentialsMapper = $credentialsMapper;
    }

    /**
     * @param string $credentialsPath
     * @return $this
     */
    public function setCredentialsPath($credentialsPath)
    {
        $this->credentialsPath = $credentialsPath;
        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Load sender`s notification services credentials
     *
     * @return $this
     * @throws DomainException
     */
    public function loadServiceCredentialsConfig()
    {
        $configPath = $this->credentialsPath;

        if (!file_exists($configPath)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Credentials file '%s' doesn`t exists",
                    $configPath
                )
            );
        }

        $config = json_decode(file_get_contents($configPath), true);

        if (!is_array($config)) {
            throw new RuntimeException('Empty credentials config');
        }

        $this->config = $config;

        return $this;
    }

    /**
     * Returns the list of names of notification services
     *
     * @return array
     */
    public function getInitializedServices()
    {
        return array_keys($this->config);
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
        if (empty($this->config)) {
            $this->loadServiceCredentialsConfig();
        }

        if (!in_array($serviceName, $this->getInitializedServices())) {
            throw new DomainException(
                sprintf("Credentials for service '%s' was not initialized", $serviceName)
            );
        }

        return
            $this
                ->credentialsMapper
                ->mapCredentials(
                    $this->getCredentialsDTOByServiceName($serviceName),
                    $this->config[$serviceName]
                )
            ;
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
