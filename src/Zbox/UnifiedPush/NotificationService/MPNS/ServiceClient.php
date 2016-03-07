<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\MPNS;

use Zbox\UnifiedPush\NotificationService\ServiceClientBase;
use Zbox\UnifiedPush\Exception\ClientException;
use Buzz\Browser;
use Buzz\Client\MultiCurl;

/**
 * Class ServiceClient
 * @package Zbox\UnifiedPush\NotificationService\MPNS
 */
class ServiceClient extends ServiceClientBase
{
    /**
     * Initializing HTTP client
     *
     * @return $this
     */
    protected function createClient()
    {
        $client = new MultiCurl();

        $credentials      = $this->getCredentials();
        $isAuthenticated  = $credentials->isAuthenticated();

        $client->setVerifyPeer($isAuthenticated);

        if ($isAuthenticated) {
            $client->setOption(CURLOPT_SSLCERT, $credentials->getCertificatePassPhrase());
            $client->setOption(CURLOPT_SSLCERTPASSWD, $credentials->getCertificatePassPhrase());
        }

        $this->serviceClient = new Browser($client);

        return $this;
    }

    /**
     * @throws ClientException
     * @return bool
     */
    public function sendRequest()
    {
        $notification = $this->getNotificationOrThrowException();

        try {
            $connection  = $this->getClientConnection();
            $serviceURL  = $this->getServiceURL();
            $url         = str_replace('[TOKEN]', $notification['recipients'][0], $serviceURL['url']);

            $headers = array();
            $headers[] = 'Accept: application/*';
            $headers[] = 'Content-Type: text/xml';

            foreach ($notification['options'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }

            $response = $connection->post($url, $headers, $notification['body']);
            $connection->getClient()->flush();

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        new Response($response, $notification['recipients']);

        return true;
    }
}
