<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\GCM;

use Zbox\UnifiedPush\Message\RecipientDevice;
use Zbox\UnifiedPush\Exception\InvalidRecipientException;
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
     * @param $recipients
     */
    public function __construct(Buzz\Message\MessageInterface $response, array $recipients)
    {
        $statusCode = $response->getStatusCode();
        $this->checkResponseCode($statusCode);

        $encodedMessage = $response->getContent();
        $message = json_decode($encodedMessage, true);
        $this->parseResponseMessage($message, $recipients);
    }

    /**
     * Checks if response has succeed code or request was rejected
     *
     * @param int $responseCode
     * @throws MalformedNotificationException
     * @throws DispatchMessageException
     * @throws RuntimeException
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
     * Parse response message
     *
     * @param \stdClass $message
     * @param array $recipients
     * @return $this
     */
    private function parseResponseMessage($message, $recipients)
    {
        if (!$message || $message->success == 0 || $message->falure > 0) {
            throw new DispatchMessageException(
                sprintf("%d messages could not be processed", $message->falure)
            );
        }

        $hasDeviceError = false;

        for ($i = 0; $i <= count($recipients); $i++) {
            if (isset($message->results[$i]['registration_id'])) {
                $hasDeviceError = true;
                $recipients[$i]->setIdentifierToReplaceTo($message->results[$i]['registration_id']);
            }

            if (isset($message->results[$i]['error'])) {
                $hasDeviceError = true;
                $error = $message->results[$i]['error'];
                $recipients[$i] = $this->processError($error, $recipients[$i]);
            }
        }

        if ($hasDeviceError) {
            throw new InvalidRecipientException("Device identifier error status", $recipients);
        }

        return $this;
    }

    private function processError($error, $recipient)
    {
        switch ($error) {
            case 'InvalidRegistration':
            case 'NotRegistered':
                $recipient->setIdentifierStatus(RecipientDevice::DEVICE_NOT_REGISTERED);
                break;

            case 'Unavailable':
                $recipient->setIdentifierStatus(RecipientDevice::DEVICE_NOT_READY);
                break;

            default:
                break;
        }

        return $recipient;
    }
}