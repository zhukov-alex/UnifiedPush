<?php

namespace Zbox\UnifiedPush;

use Zbox\UnifiedPush\NotificationService\CredentialsTest;
use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsMapper;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CREDENTIALS_PATH = 'credentials.test.json';
    const TEST_APPLICATION_NAME = 'TestApplicationName';

    public function setUp()
    {
        $this->application = $this->getMockBuilder('\Zbox\UnifiedPush\Application')
            ->setConstructorArgs(
                array(
                    self::TEST_APPLICATION_NAME,
                    new CredentialsMapper()
                )
            )
            ->setMethods(array('getCredentialsFilepath'))
            ->getMock();

        $this->application
            ->expects($this->once())
            ->method('getCredentialsFilepath')
            ->will($this->returnValue($this->getAppConfigPath()));

        $this->application->loadApplicationConfig();
    }

    /**
     * @dataProvider credentialsByServiceProvider
     * @param string $service
     * @param array $credentials
     */
    public function testGetCredentialsByService($service, $credentials)
    {
        $this->assertEquals(
            $this->application->getCredentialsByService($service),
            CredentialsTest::createCredentialsOfType($service, $credentials)
        );
    }

    /**
     * Credentials data provider
     */
    public static function credentialsByServiceProvider()
    {
        return array(
            array(
                'APNS', array(
                    "certificate"           => "certificate.test.pem",
                    "certificatePassPhrase" => "certificatePassPhrase"
                )
            ),
            array(
                'GCM', array("authToken" => "authToken")
            )
        );
    }

    /**
     * @dataProvider initializedServicesProvider
     * @param array $services
     */
    public function testGetInitializedServices($services)
    {
        $this->assertEquals(
            $this->application->getInitializedServices(),
            $services
        );
    }

    /**
     * Initialized services data provider
     */
    public static function initializedServicesProvider()
    {
        return array(
            'InitializedServices' => array(
                array('APNS', 'GCM', 'MPNS')
            )
        );
    }

    /**
     *
     * @return string
     */
    private function getAppConfigPath()
    {
        return __DIR__
            . DIRECTORY_SEPARATOR . 'Resources'
            . DIRECTORY_SEPARATOR . self::TEST_CREDENTIALS_PATH;
    }
}
