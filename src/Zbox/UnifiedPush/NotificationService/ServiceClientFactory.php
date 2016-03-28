<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Exception\InvalidArgumentException;
use Zbox\UnifiedPush\Exception\RuntimeException;
use Zbox\UnifiedPush\Exception\DomainException;

/**
 * Class ServiceClientFactory
 * @package Zbox\UnifiedPush\NotificationService
 */
class ServiceClientFactory
{
    const ENVIRONMENT_PRODUCTION   = 'production';
    const ENVIRONMENT_DEVELOPMENT  = 'development';

    const PUSH_SERVICE             = 'push';
    const FEEDBACK_SERVICE         = 'feedback';

    /**
     * Type of environment
     *
     * @var string
     */
    private $environment;

    /**
     * @var ServiceCredentialsFactory
     */
    private $credentialsFactory;

    /**
     * @var string
     */
    private $serviceConfigPath;

    /**
     * Notification services connection config
     *
     * @var array
     */
    private $serviceConfig;

    /**
     * @param ServiceCredentialsFactory $credentialsFactory
     */
    public function __construct(ServiceCredentialsFactory $credentialsFactory)
    {
        $this->setEnvironment(self::ENVIRONMENT_PRODUCTION);

        $this->credentialsFactory = $credentialsFactory;

        return $this;
    }

    /**
     * @param bool $isDevelopment
     * @return $this
     */
    public function setDevelopmentMode($isDevelopment)
    {
        $this->environment = (bool) $isDevelopment ?
            self::ENVIRONMENT_DEVELOPMENT :
            self::ENVIRONMENT_PRODUCTION;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param string $serviceConfigPath
     * @return $this
     */
    public function setServiceConfigPath($serviceConfigPath)
    {
        $this->serviceConfigPath = $serviceConfigPath;
        return $this;
    }

    /**
     * @return $this
     */
    public function setDefaultConfigPath()
    {
        $path =
            __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Resources'
            . DIRECTORY_SEPARATOR . 'notification_services.json';

        $this->setServiceConfigPath($path);
        return $this;
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        if (empty($this->serviceConfig)) {
            $this->loadServicesConfig();
        }

        return $this->serviceConfig;
    }

    /**
     * Gets notification service url by service name
     *
     * @param string $serviceName
     * @param bool $isFeedback
     * @return array
     */
    public function getServiceURL($serviceName, $isFeedback = false)
    {
        $serviceName = NotificationServices::validateServiceName($serviceName);
        $serviceType = $isFeedback ? self::FEEDBACK_SERVICE : self::PUSH_SERVICE;
        $environment = $this->getEnvironment();

        $serviceConfig = $this->getServiceConfig();

        if (empty($serviceConfig[$serviceName][$serviceType][static::ENVIRONMENT_DEVELOPMENT])) {
            $environment = static::ENVIRONMENT_PRODUCTION;
        }

        if (empty($serviceConfig[$serviceName][$serviceType][$environment])) {
            throw new DomainException("Service url is not defined");
        }

        return $serviceConfig[$serviceName][$serviceType][$environment];
    }

    /**
     * Creates client server connection by service name and sender credentials
     *
     * @param string $serviceName
     * @param bool $isFeedback
     * @return ServiceClientInterface
     */
    public function createServiceClient($serviceName, $isFeedback = false)
    {
        $serviceUrl = $this->getServiceURL($serviceName, $isFeedback);

        $clientClass = sprintf(
            'Zbox\UnifiedPush\NotificationService\%s\%s',
            $serviceName,
            $isFeedback ? 'ServiceFeedbackClient' : 'ServiceClient'
        );

        $credentials        = $this->credentialsFactory->getCredentialsByService($serviceName);
        $clientConnection   = new $clientClass($serviceUrl, $credentials);

        return $clientConnection;
    }

    /**
     * Load notification services connection data
     *
     * @return $this
     */
    public function loadServicesConfig()
    {
        $configPath = $this->serviceConfigPath;

        if (!file_exists($configPath)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Service config file '%s' doesn`t exists",
                    $configPath
                )
            );
        }

        $config = json_decode(file_get_contents($configPath), true);

        if (!is_array($config)) {
            throw new RuntimeException('Empty credentials config');
        }

        $this->serviceConfig = $config;

        return $this;
    }
}
