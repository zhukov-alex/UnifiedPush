<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\APNS;

use Zbox\UnifiedPush\Exception\ClientException;

/**
 * Class ServiceFeedbackClient
 * @package Zbox\UnifiedPush\NotificationService\APNS
 */
class ServiceFeedbackClient extends ServiceClient
{
    /**
     * APN Feedback service give you information about failed push notifications
     *
     * @param array|null $notification
     * @throws ClientException
     * @return bool
     */
    /**
     * @return \ArrayIterator
     */
    public function sendRequest()
    {
        try {
            $connection    = $this->getClientConnection();
            $feedbackData  = $connection->read(-1);
            $connection->disconnect();

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        return new ResponseFeedback($feedbackData, new \ArrayIterator());
    }
}
