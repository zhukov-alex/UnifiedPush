<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\GCM;

use Zbox\UnifiedPush\NotificationService\ResponseInterface;
use Zbox\UnifiedPush\Message\RecipientDevice;
use Zbox\UnifiedPush\Exception\InvalidRecipientException;
use Zbox\UnifiedPush\Exception\DispatchMessageException;
use Zbox\UnifiedPush\Exception\MalformedNotificationException;
use Zbox\UnifiedPush\Exception\RuntimeException;

/**
 * Class Response
 * @package Zbox\UnifiedPush\NotificationService\GCM
 */
class Response implements ResponseInterface
{
    const REQUEST_HAS_SUCCEED_CODE     = 200;
    const MALFORMED_NOTIFICATION_CODE  = 400;
    const AUTHENTICATION_ERROR_CODE    = 401;

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
        $response   = $this->response;
        $statusCode = $response->getStatusCode();

        $this->checkResponseCode($statusCode);

        $encodedMessage = $response->getContent();
        $message = $this->decodeMessage($encodedMessage);

        $this->checkMessageStatus($message);
        $this->checkMessageResult($message, $this->recipients);
    }

    /**
     * @param string $json
     * @return \stdClass
     */
    public function decodeMessage($json)
    {
        $message = json_decode($json);

        if (is_null($message)) {
            throw new RuntimeException("Message could not be decoded");
        }

        return $message;
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
                    "The request could not be parsed as JSON, or it contained invalid fields",
                    self::MALFORMED_NOTIFICATION_CODE
                );

            case self::AUTHENTICATION_ERROR_CODE:
                throw new DispatchMessageException(
                    "There was an error authenticating the sender account.",
                    self::AUTHENTICATION_ERROR_CODE
                );

            default:
                throw new RuntimeException(
                    "Unknown error occurred while sending notification."
                );
        }
    }

    /**
     * Checks message status
     *
     * @param \stdClass $message
     */
    private function checkMessageStatus($message)
    {
        if (!$message || $message->success == 0 || $message->failure > 0) {
            throw new DispatchMessageException(
                sprintf("%d messages could not be processed", $message->failure)
            );
        }
    }

    /**
     * Check message result
     *
     * @param \stdClass $message
     * @param \ArrayIterator $recipients
     */
    private function checkMessageResult($message, \ArrayIterator $recipients)
    {
        $hasDeviceError = false;

        $recipientCount = $recipients->count();

        for ($i = 0; $i <= $recipientCount; $i++) {
            if (isset($message->results[$i]['registration_id'])) {
                $hasDeviceError = true;
                $recipients->offsetGet($i)->setIdentifierToReplaceTo($message->results[$i]['registration_id']);
            }

            if (isset($message->results[$i]['error'])) {
                $hasDeviceError = true;
                $error = $message->results[$i]['error'];
                $this->processError($error, $recipients->offsetGet($i));
            }
        }

        if ($hasDeviceError) {
            throw new InvalidRecipientException("Device identifier error status", $recipients);
        }
    }

    /**
     * @param string $error
     * @param RecipientDevice $recipient
     * @return RecipientDevice
     */
    private function processError($error, RecipientDevice $recipient)
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
