<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\APNS;

use Zbox\UnifiedPush\NotificationService\ServiceClientBase;
use Zbox\UnifiedPush\Exception\ClientException;
use Zbox\UnifiedPush\Utils\SocketClient;

/**
 * Class ServiceClient
 * @package Zbox\UnifiedPush\NotificationService\APNS
 */
class ServiceClient extends ServiceClientBase
{
    const SECURE_TRANSPORT_DEFAULT = 'ssl';

    /**
     * Initializing socket client
     *
     * @return $this
     */
    protected function createClient()
    {
        /** @var Credentials $credentials */
        $credentials         = $this->getCredentials();
        $url                 = $this->getServiceURL();
        $transport           = !empty($url['transport']) ? $url['transport'] : self::SECURE_TRANSPORT_DEFAULT;

        $this->serviceClient = new SocketClient($url['host'], $url['port']);
        $this->serviceClient
            ->setTransport($transport)
            ->setContextOptions(array(
                'local_cert' => $credentials->getCertificate(),
                'passphrase' => $credentials->getCertificatePassPhrase(),
            ))
            ->addConnectionFlag(STREAM_CLIENT_PERSISTENT)
            ->setBlockingMode(false)
        ;

        return $this;
    }

    /**
     * Gets socket connection (reestablish, if needed)
     *
     * @return SocketClient
     */
    public function getClientConnection()
    {
        if (!$this->serviceClient) {
            $this->createClient();
        }

        if (!$this->serviceClient->isAlive()) {
            $this->serviceClient->connect();
        }

        return $this->serviceClient;
    }

    /**
     * If you send a notification that is accepted by APNs,
     * nothing is returned. If you send a notification that is malformed
     * or otherwise unintelligible, APNs returns an error-response packet
     *
     * @throws ClientException
     * @return bool
     */
    public function sendRequest()
    {
        $notification = $this->getNotificationOrThrowException();

        try {
            $connection = $this->getClientConnection();
            $connection->write($notification->getPayload());

            $errorResponseData = $connection->read(Response::ERROR_RESPONSE_LENGTH);

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        if ($errorResponseData) {
            new Response($errorResponseData, $notification->getRecipients());
        }
        return true;
    }
}
