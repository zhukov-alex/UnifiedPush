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
    const SECURE_TRANSPORT_DEFAULT = 'tls';

    /**
     * Initializing socket client
     *
     * @return $this
     */
    protected function createClient()
    {
        $credentials         = $this->getCredentials();
        $url                 = $this->getServiceURL();
        $this->serviceClient = new SocketClient(self::SECURE_TRANSPORT_DEFAULT, $url['host'], $url['port']);
        $this->serviceClient
            ->setContextOptions(array(
                    'local_cert' => $credentials->getCertificate(),
                    'passphrase' => $credentials->getCertificatePassPhrase(),
            ))
            ->setBlockingMode(false)
        ;

        return $this;
    }

    /**
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
     * @param string $notification
     * @throws ClientException
     * @return bool
     */
    public function sendNotification($notification)
    {
        try {
            $connection = $this->getClientConnection();
            $connection->write($notification);

            $errorResponseData = $connection->read(Response::ERROR_RESPONSE_LENGTH);

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        if ($errorResponseData) {
            new Response($errorResponseData);
        }
        return true;
    }

    /**
     * APN Feedback service give you information about failed push notifications
     *
     * @throws ClientException
     * @return \ArrayIterator
     */
    public function readFeedback()
    {
        try {
            $connection    = $this->getClientConnection();
            $feedbackData  = $connection->read(-1);
            $connection->disconnect();

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        $refusedRecipients = new \ArrayIterator();

        foreach (str_split($feedbackData, 38) as $item)
        {
            $deviceData = unpack('N1timestamp/n1length/H*token', $item);
            $refusedRecipients->append($deviceData['token']);
        }

        return $refusedRecipients;
    }
}
