<?php

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\SSLCertificate;

class ServiceClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigInitialization()
    {
        $clientFactory = new ServiceClientFactory(
            $this->getCredentialsFactoryMock()
        );
        $clientFactory->setDevelopmentMode(true);
        $clientFactory->setServiceConfigPath($this->getPathToServicesConfig());

        $apns = NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE;
        $options = $clientFactory->getServiceURL($apns);

        $this->assertSame($options['port'], 2195);
    }

    public function testSetDevelopmentMode()
    {
        $clientFactory =
            new ServiceClientFactory(
                $this->getCredentialsFactoryMock()
            );

        $environmentProd = ServiceClientFactory::ENVIRONMENT_PRODUCTION;
        $environmentDev  = ServiceClientFactory::ENVIRONMENT_DEVELOPMENT;

        $clientFactory->setEnvironment($environmentDev);
        $this->assertTrue($clientFactory->getEnvironment() == $environmentDev);

        $clientFactory->setDevelopmentMode(false);
        $this->assertTrue($clientFactory->getEnvironment() == $environmentProd);
    }

    public function testCreateServiceClient()
    {
        $credentialsFactoryMock = $this->getCredentialsFactoryMock();

        $factory = $this->getMockBuilder('\Zbox\UnifiedPush\NotificationService\ServiceClientFactory')
            ->setConstructorArgs(array($credentialsFactoryMock))
            ->setMethods(array('getServiceURL', 'getEnvironment'))
            ->getMock();

        $serviceName = NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE;

        $serviceUrl  = array(
            'host' => 'gateway.sandbox.push.apple.com',
            'port' => 2195
        );

        $factory
            ->expects($this->once())
            ->method('getServiceURL')
            ->will($this->returnValue($serviceUrl));

        $factory
            ->expects($this->any())
            ->method('getEnvironment')
            ->with($this->equalTo(ServiceClientFactory::ENVIRONMENT_PRODUCTION));

        $client = $factory->createServiceClient($serviceName, $this->getAPNSCredentialsStub(), false);
        $this->assertInstanceOf('Zbox\UnifiedPush\NotificationService\APNS\ServiceClient', $client);
    }

    protected function getAPNSCredentialsStub()
    {
        return
            CredentialsTest::createCredentialsOfType(
                NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE,
                array(
                    'certificate' => APNSServiceClientTest::getPathToCertificate(),
                    'certificatePassPhrase' => 'certificatePassPhrase'
                )
            );
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

    protected function getCredentialsFactoryMock()
    {
        $credentialsFactoryMock =
            $this
                ->getMockBuilder(
                    '\Zbox\UnifiedPush\NotificationService\ServiceCredentialsFactory'
                )
                ->disableOriginalConstructor()
                ->setMethods(array('getCredentialsByService'))
                ->getMock();

        $credentialsFactoryMock
            ->expects($this->any())
            ->method('getCredentialsByService')
            ->will($this->returnValue(new SSLCertificate()));

        return $credentialsFactoryMock;
    }
}
