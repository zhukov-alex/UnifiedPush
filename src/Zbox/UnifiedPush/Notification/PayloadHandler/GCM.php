<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification\PayloadHandler;

use Zbox\UnifiedPush\Message\MessageInterface;
use Zbox\UnifiedPush\Notification\PayloadHandler;
use Zbox\UnifiedPush\Message\Type\GCM as GCMMessage;
use Zbox\UnifiedPush\Utils\JsonEncoder;

/**
 * Class GCM
 * @package Zbox\UnifiedPush\Notification\PayloadHandler
 */
class GCM extends PayloadHandler
{
    /**
     * The maximum size allowed for an Android notification payload is 4096 bytes
     */
    const PAYLOAD_MAX_LENGTH = 4096;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function isSupported(MessageInterface $message)
    {
        return $message instanceof GCMMessage;
    }

    /**
     * @return array
     */
    public function createPayload()
    {
        $registrationIds = array();

        /** @var GCMMessage $message */
        $message = $this->message;

        foreach ($message->getRecipientDeviceCollection() as $recipient) {
            $registrationIds[] = $recipient->getIdentifier();
        }

        $payload =
            array(
                'collapse_key'      => $message->getCollapseKey(),
                'delay_while_idle'  => $message->isDelayWhileIdle(),
                'registration_ids'  => $registrationIds,
                'data'              => $message->getPayloadData(),
                'time_to_live'      => $message->getExpirationTime()->format('U') - time()
            );

        return $this->checkIfDryRun($message, $payload);
    }

    /**
     * Pack message body into a json representation
     *
     * @param array $payload
     * @return string
     */
    public function packPayload($payload)
    {
        return JsonEncoder::jsonEncode($payload);
    }

    /**
     * @param GCMMessage $message
     * @param array $payload
     * @return array
     */
    protected function checkIfDryRun(GCMMessage $message, $payload)
    {
        if ($message->isDryRun()) {
            $payload['dry_run'] = $message->isDryRun();
        }

        return $payload;
    }
}
