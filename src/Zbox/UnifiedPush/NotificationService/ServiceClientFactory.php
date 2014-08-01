<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService;

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
     * @var string
     */
    private $environment;

    /**
     * @var array
     */
    private $config;


    public function __construct()
    {
        $this->setEnvironment(self::ENVIRONMENT_PRODUCTION);
        $this->loadServicesConfig();
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
     * @param string $serviceName
     * @param bool $isFeedback
     * @return array
     */
    public function getServiceURL($serviceName, $isFeedback = false)
    {
        $serviceName = NotificationServices::validateServiceName($serviceName);
        $serviceType = $isFeedback ? self::FEEDBACK_SERVICE : self::PUSH_SERVICE;
        $environment = $this->getEnvironment();

        if (empty($this->config[$serviceName][$serviceType][$environment])) {
            throw new DomainException("Service url is not defined");
        }

        return $this->config[$serviceName][$serviceType][$environment];
    }

    /**
     * @param string $serviceName
     * @param array $credentials
     * @param bool $isFeedback
     * @return ServiceClientInterface
     */
    public function createServiceClient($serviceName, $credentials, $isFeedback = false)
    {
        $serviceUrl = $this->getServiceURL($serviceName, $isFeedback);

        $credentialsClass   = 'Zbox\UnifiedPush\NotificationService\\' . $serviceName . '\Credentials';
        $clientClass        = 'Zbox\UnifiedPush\NotificationService\\' . $serviceName . '\ServiceClient';

        $credentials        = new $credentialsClass($credentials);
        $clientConnection   = new $clientClass($serviceUrl, $credentials);

        return $clientConnection;
    }

    /**
     * @return $this
     */
    protected function loadServicesConfig()
    {
        $filePath = __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Resources'
            . DIRECTORY_SEPARATOR . 'notification_services.json';

        $this->config = json_decode(file_get_contents($filePath), true);

        return $this;
    }
}
