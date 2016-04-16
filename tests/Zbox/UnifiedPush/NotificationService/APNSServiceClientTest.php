<?php

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\SSLCertificate as APNSCredentials;
use Zbox\UnifiedPush\NotificationService\APNS\ServiceClient;

class APNSServiceClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceClient
     */
    protected $client;

    public function setUp()
    {
        $credentials = array(
            'certificate' => self::getPathToCertificate(),
            'certificatePassPhrase' => 'certificatePassPhrase'
        );

        $serviceUrl = array(
            'host'=> 'gateway.sandbox.push.apple.com',
            'port' => 2195
        );

        $credentialsObj = new APNSCredentials($credentials);
        $this->client   = new ServiceClient($serviceUrl, $credentialsObj);
    }

    public function testCreation()
    {
        $client = $this->client;

        $this->assertInstanceOf('Zbox\UnifiedPush\Utils\ClientCredentials\DTO\SSLCertificate', $client->getCredentials());

        $url = $client->getServiceURL();
        $this->assertTrue($url['port'] == 2195);
    }

    /**
     * @return string
     */
    public static function getPathToCertificate()
    {
        return __DIR__
        . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . 'Resources'
        . DIRECTORY_SEPARATOR . 'certificate.test.pem';
    }
}
