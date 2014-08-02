<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\APNS;

use Zbox\UnifiedPush\Exception\DispatchMessageException;
use Zbox\UnifiedPush\Exception\RuntimeException;

/**
 * Class Response
 * @package Zbox\UnifiedPush\NotificationService\APNS
 */
class Response
{
    /**
     * APNS error-response packet length
     */
    const ERROR_RESPONSE_LENGTH = 6;

    /**
     * APNS error-response packet command
     */
    const ERROR_RESPONSE_COMMAND = 8;

    const NO_ERROR = 0;
    const ERROR_PROCESSING = 1;
    const ERROR_MISSING_DEVICE_TOKEN = 2;
    const ERROR_MISSING_TOPIC = 3;
    const ERROR_MISSING_PAYLOAD = 4;
    const ERROR_INVALID_TOKEN_SIZE = 5;
    const ERROR_INVALID_TOPIC_SIZE = 6;
    const ERROR_INVALID_PAYLOAD_SIZE = 7;
    const ERROR_INVALID_TOKEN = 8;
    const ERROR_SHUTDOWN = 10;
    const ERROR_UNKNOWN = 255;

    /**
     * @var array
     */
    private static $responseDescription = array(
        self::NO_ERROR => 'No errors encountered',
        self::ERROR_PROCESSING => 'Processing error',
        self::ERROR_MISSING_DEVICE_TOKEN => 'Missing device token',
        self::ERROR_MISSING_TOPIC => 'Missing topic',
        self::ERROR_MISSING_PAYLOAD => 'Missing payload',
        self::ERROR_INVALID_TOKEN_SIZE => 'Invalid token size',
        self::ERROR_INVALID_TOPIC_SIZE => 'Invalid topic size',
        self::ERROR_INVALID_PAYLOAD_SIZE => 'Invalid payload size',
        self::ERROR_INVALID_TOKEN => 'Invalid token',
        self::ERROR_SHUTDOWN => 'Shutdown',
        self::ERROR_UNKNOWN => 'None (unknown)',
    );

    /**
     * @param string $binaryData
     */
    public function __construct($binaryData)
    {
        $this->parseResponse($binaryData);
    }

    /**
     * Unpacks response data
     *
     * @param string $binaryData
     */
    public function parseResponse($binaryData)
    {
        $responseData = unpack("Ccommand/Cstatus/Nidentifier", $binaryData);

        $this->validateResponse($responseData);

        $statusCode = $responseData['status'];
        $errorDescription = self::$responseDescription[$statusCode];

        throw new DispatchMessageException($errorDescription, $statusCode);
    }

    /**
     * Validates response data
     *
     * @param string $responseData
     * @return $this
     */
    public function validateResponse($responseData)
    {
        if ($responseData === false) {
            throw new RuntimeException('Unable to unpack response data');
        }

        if (self::ERROR_RESPONSE_COMMAND != $responseData['command']) {
            throw new RuntimeException("Invalid APNS response packet command");
        }

        return $this;
    }
}

