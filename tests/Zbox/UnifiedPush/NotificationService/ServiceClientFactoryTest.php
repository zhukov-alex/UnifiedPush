<?php

namespace Zbox\UnifiedPush\NotificationService;

class ServiceClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigInitialization()
    {
        $clientFactory = new ServiceClientFactory();

        $prorertyReflection = new \ReflectionProperty($clientFactory, 'config');
        $prorertyReflection->setAccessible(true);

        $this->assertTrue(array_key_exists(
            NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE,
            $prorertyReflection->getValue($clientFactory)
        ));
    }

    public function testSetDevelopmentMode()
    {
        $clientFactory = new ServiceClientFactory();

        $environmentProd = ServiceClientFactory::ENVIRONMENT_PRODUCTION;
        $environmentDev  = ServiceClientFactory::ENVIRONMENT_DEVELOPMENT;

        $clientFactory->setEnvironment($environmentDev);
        $this->assertTrue($clientFactory->getEnvironment() == $environmentDev);

        $clientFactory->setDevelopmentMode(false);
        $this->assertTrue($clientFactory->getEnvironment() == $environmentProd);
    }

    public function testCreateServiceClient()
    {
        $factory = $this->getMockBuilder('\Zbox\UnifiedPush\NotificationService\ServiceClientFactory')
            ->setMethods(array('getServiceURL', 'getEnvironment'))
            ->getMock();

        $serviceName = NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE;

        $credentials = array(
            'certificate' => APNSServiceClientTest::getPathToCertificate(),
            'certificatePassPhrase' => 'certificatePassPhrase'
        );

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

        $client = $factory->createServiceClient($serviceName, $credentials, false);
        $this->assertInstanceOf('Zbox\UnifiedPush\NotificationService\APNS\ServiceClient', $client);
    }
}