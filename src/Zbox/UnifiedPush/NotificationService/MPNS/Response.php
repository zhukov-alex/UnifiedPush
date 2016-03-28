<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\MPNS;

use Zbox\UnifiedPush\NotificationService\ResponseInterface;
use Zbox\UnifiedPush\Message\RecipientDevice;
use Zbox\UnifiedPush\Exception\InvalidRecipientException;
use Zbox\UnifiedPush\Exception\DispatchMessageException;
use Zbox\UnifiedPush\Exception\MalformedNotificationException;
use Zbox\UnifiedPush\Exception\RuntimeException;

/**
 * Class Response
 * @package Zbox\UnifiedPush\NotificationService\MPNS
 */
class Response implements ResponseInterface
{
    const REQUEST_HAS_SUCCEED_CODE       = 200;
    const MALFORMED_NOTIFICATION_CODE    = 400;
    const AUTHENTICATION_ERROR_CODE      = 401;
    const INVALID_RECIPIENT_ERROR_CODE   = 404;
    const INVALID_METHOD_ERROR_CODE      = 405;
    const QUOTA_EXCEEDED_ERROR_CODE      = 406;
    const DEVICE_INACTIVE_ERROR_CODE     = 412;
    const SERVER_UNAVAILABLE_ERROR_CODE  = 503;

    /**
     * @var \Buzz\Message\Response
     */
    protected $response;

    /**
     * @var \ArrayIterator
     */
    protected $recipients;

    /**
     * @param \Buzz\Message\Response $response
     * @param \ArrayIterator $recipients
     */
    public function __construct(\Buzz\Message\Response $response, \ArrayIterator $recipients)
    {
        $this->response     = $response;
        $this->recipients   = $recipients;
    }

    /**
     * {@inheritdoc}
     */
    public function processResponse()
    {
        $statusCode = $this->response->getStatusCode();
        $this->checkResponseCode($statusCode, $this->recipients);
    }

    /**
     * Checks if response has succeed code or request was rejected
     *
     * @param int $responseCode
     * @param \ArrayIterator $recipients
     * @throws \Zbox\UnifiedPush\Exception\MalformedNotificationException
     * @throws \Zbox\UnifiedPush\Exception\DispatchMessageException
     * @throws \Zbox\UnifiedPush\Exception\RuntimeException
     */
    private function checkResponseCode($responseCode, \ArrayIterator $recipients)
    {
        switch ($responseCode) {
            case self::REQUEST_HAS_SUCCEED_CODE:
                break;

            case self::MALFORMED_NOTIFICATION_CODE:
                throw new MalformedNotificationException(
                    "Notification request with a bad XML document or malformed notification URI"
                );

            case self::AUTHENTICATION_ERROR_CODE:
                throw new DispatchMessageException(
                    "Sending this notification is unauthorized", self::AUTHENTICATION_ERROR_CODE
                );

            case self::INVALID_RECIPIENT_ERROR_CODE:
                $recipients->current()->setIdentifierStatus(RecipientDevice::DEVICE_NOT_REGISTERED);

                throw new InvalidRecipientException(
                    "The subscription is invalid and is not present on the Push Notification Service",
                    $recipients
                );

            case self::INVALID_METHOD_ERROR_CODE:
                throw new DispatchMessageException(
                    "Invalid method. Only POST is allowed when sending a notification request",
                    self::INVALID_METHOD_ERROR_CODE
                );

            case self::QUOTA_EXCEEDED_ERROR_CODE:
                throw new DispatchMessageException(
                    "Unauthenticated service has reached the per-day throttling limit or there are many notifications per second",
                    self::QUOTA_EXCEEDED_ERROR_CODE
                );

            case self::DEVICE_INACTIVE_ERROR_CODE:
                throw new DispatchMessageException(
                    "The device is in a disconnected state",
                    self::DEVICE_INACTIVE_ERROR_CODE
                );

            case self::SERVER_UNAVAILABLE_ERROR_CODE:
                throw new DispatchMessageException(
                    "The Push Notification Service is unable to process the request",
                    self::SERVER_UNAVAILABLE_ERROR_CODE
                );

            default:
                throw new RuntimeException(
                    "Unknown error occurred while sending notification."
                );
        }
    }
}
