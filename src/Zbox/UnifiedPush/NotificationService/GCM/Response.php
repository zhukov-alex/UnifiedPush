<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\GCM;

use Zbox\UnifiedPush\Exception\DispatchMessageException;
use Zbox\UnifiedPush\Exception\MalformedNotificationException;
use Zbox\UnifiedPush\Exception\RuntimeException;

/**
 * Class Response
 * @package Zbox\UnifiedPush\NotificationService\GCM
 */
class Response
{
    const REQUEST_HAS_SUCCEED_CODE     = 200;
    const MALFORMED_NOTIFICATION_CODE  = 400;
    const AUTHENTICATION_ERROR_CODE    = 401;

    /**
     * @param Buzz\Message\MessageInterface $response
     */
    public function __construct(Buzz\Message\MessageInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $this->checkResponseCode($statusCode);

        $encodedMessage = $response->getContent();
        $message = json_decode($encodedMessage, true);
        $this->parseResponseMessage($message);
    }

    /**
     * @param int $responseCode
     * @throws \Zbox\UnifiedPush\Exception\MalformedNotificationException
     * @throws \Zbox\UnifiedPush\Exception\DispatchMessageException
     * @throws \Zbox\UnifiedPush\Exception\RuntimeException
     */
    private function checkResponseCode($responseCode)
    {
        switch ($responseCode) {
            case self::REQUEST_HAS_SUCCEED_CODE:
                break;

            case self::MALFORMED_NOTIFICATION_CODE:
                throw new MalformedNotificationException(
                    "The request could not be parsed as JSON, or it contained invalid fields"
                );
                break;

            case self::AUTHENTICATION_ERROR_CODE:
                throw new DispatchMessageException(
                    "There was an error authenticating the sender account."
                );
                break;

            default:
                throw new RuntimeException(
                    "Unknown error occurred while sending notification."
                );
                break;
        }
    }

    /**
     * @param \stdClass $message
     */
    private function parseResponseMessage($message)
    {
        if (!$message || $message->success == 0 || $message->falure > 0) {
            throw new DispatchMessageException(
                sprintf("%d messages could not be processed", $message->falure)
            );
        }
    }
}