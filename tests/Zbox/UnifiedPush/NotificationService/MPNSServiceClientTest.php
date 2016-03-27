<?php

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\NullCredentials as MPNSCredentials;
use Zbox\UnifiedPush\NotificationService\MPNS\ServiceClient;

class MPNSServiceClientTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $credentials = array();

        $serviceUrl = array(
            'host' => 'sn1.notify.live.net/throttledthirdparty/01.00/[TOKEN]',
            'port' => 80
        );

        $credentialsObj = new MPNSCredentials($credentials);
        $client         = new ServiceClient($serviceUrl, $credentialsObj);

        $this->assertInstanceOf('Zbox\UnifiedPush\Utils\ClientCredentials\DTO\NullCredentials', $client->getCredentials());

        $this->assertInstanceOf('Buzz\Browser', $client->getClientConnection());

        $url = $client->getServiceURL();
        $this->assertTrue($url['port'] == 80);
    }
}
