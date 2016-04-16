<?php

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\SSLCertificate;

class ServiceClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceClientFactory
     */
    protected $clientFactory;

    public function setUp()
    {
        return $this->clientFactory =
            new ServiceClientFactory(
                $this->getCredentialsFactoryStub()
            );
    }

    public function testConfigInitialization()
    {
        $clientFactory = $this->clientFactory;

        $clientFactory->setDevelopmentMode(true);
        $clientFactory->setServiceConfigPath($this->getPathToServicesConfig());

        $apns = NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE;
        $options = $clientFactory->getServiceURL($apns);

        $this->assertSame($options['port'], 2195);
    }

    public function testSetDevelopmentMode()
    {
        $clientFactory = $this->clientFactory;

        $environmentProd = ServiceClientFactory::ENVIRONMENT_PRODUCTION;
        $environmentDev  = ServiceClientFactory::ENVIRONMENT_DEVELOPMENT;

        $clientFactory->setEnvironment($environmentDev);
        $this->assertTrue($clientFactory->getEnvironment() == $environmentDev);

        $clientFactory->setDevelopmentMode(false);
        $this->assertTrue($clientFactory->getEnvironment() == $environmentProd);
    }

    public function testCreateServiceClient()
    {
        $clientFactory =
            $this->clientFactory
                ->setEnvironment(ServiceClientFactory::ENVIRONMENT_DEVELOPMENT)
                ->setServiceConfigPath($this->getPathToServicesConfig())
        ;

        $client = $clientFactory->createServiceClient(
            NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE,
            false
        );

        $this->assertInstanceOf('Zbox\UnifiedPush\NotificationService\APNS\ServiceClient', $client);
    }

    /**
     * @return string
     */
    public static function getPathToServicesConfig()
    {
        return __DIR__
        . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . 'Resources'
        . DIRECTORY_SEPARATOR . 'services.test.json';
    }

    protected function getCredentialsFactoryStub()
    {
        $credentialsFactory =
            $this
                ->getMockBuilder(
                    '\Zbox\UnifiedPush\NotificationService\ServiceCredentialsFactory'
                )
                ->disableOriginalConstructor()
                ->setMethods(array('getCredentialsByService'))
                ->getMock();

        $credentialsFactory
            ->expects($this->any())
            ->method('getCredentialsByService')
            ->will($this->returnValue(new SSLCertificate()));

        return $credentialsFactory;
    }
}
