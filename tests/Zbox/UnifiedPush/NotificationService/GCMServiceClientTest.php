<?php

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\NotificationService\GCM\Credentials as GCMCredentials;
use Zbox\UnifiedPush\NotificationService\GCM\ServiceClient;

class GCMServiceClientTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $credentials = array(
            'authToken' => 'testToken'
        );

        $serviceUrl = array(
            'host' => 'android.googleapis.com/gcm/send',
            'port' => 443
        );

        $credentialsObj = new GCMCredentials($credentials);
        $client         = new ServiceClient($serviceUrl, $credentialsObj);

        $this->assertInstanceOf('Zbox\UnifiedPush\NotificationService\CredentialsInterface', $client->getCredentials());
        $this->assertInstanceOf('Buzz\Browser', $client->getClientConnection());

        $url = $client->getServiceURL();
        $this->assertTrue($url['port'] == 443);
    }
}
