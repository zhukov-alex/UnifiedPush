<?php

namespace Zbox\UnifiedPush;


class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CREDENTIALS_PATH = 'credentials.test.json';
    const TEST_APPLICATION_NAME = 'TestApplicationName';

    public function setUp()
    {
        $this->application = $this->getMockBuilder('\Zbox\UnifiedPush\Application')
            ->disableOriginalConstructor()
            ->setMethods(array('getCredentialsFilepath'))
            ->getMock();

        $this->application
            ->expects($this->once())
            ->method('getCredentialsFilepath')
            ->will($this->returnValue($this->getAppConfigPath()));

        $this->application->loadApplicationConfig(self::TEST_APPLICATION_NAME);
    }

    /**
     * @dataProvider credentialsByServiceProvider
     */
    public function testGetCredentialsByService($service, $credentials)
    {
        $this->assertEquals(
            $this->application->getCredentialsByService($service),
            $credentials
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
                array('APNS', 'GCM')
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