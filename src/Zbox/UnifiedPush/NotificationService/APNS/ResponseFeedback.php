<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\APNS;

use Zbox\UnifiedPush\NotificationService\ResponseInterface;
use Zbox\UnifiedPush\Message\RecipientDevice;
use Zbox\UnifiedPush\Message\Type\APNS as APNSMessage;

/**
 * Class ResponseFeedback
 * @package Zbox\UnifiedPush\NotificationService\APNS
 */
class ResponseFeedback implements ResponseInterface
{
    /**
     * @var string
     */
    protected $rawResponse;

    /**
     * @var \ArrayIterator
     */
    protected $recipients;

    /**
     * @param string $binaryData
     * @param \ArrayIterator $recipients
     */
    public function __construct($binaryData, \ArrayIterator $recipients)
    {
        $this->rawResponse = $binaryData;
        $this->recipients  = $recipients;
    }

    /**
     * @return \ArrayIterator
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * {@inheritdoc}
     */
    public function processResponse()
    {
        $rawResponse = $this->rawResponse;

        if (empty($rawResponse)) {
            return;
        }

        foreach (str_split($rawResponse, 38) as $item)
        {
            $deviceData = unpack('N1timestamp/n1length/H*token', $item);

            $this->recipients->append(
                new RecipientDevice($deviceData['token'], new APNSMessage())
            );
        }
    }
}
