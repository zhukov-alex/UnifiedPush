<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message;

interface MessageInterface
{
    public function getMessageIdentifier();

    public function getMessageType();

    public function getPayloadMaxLength();

    public function getMaxRecipientsPerMessage();

    public function getRecipient();

    public function packMessage($message, $recipients);

    public function validateRecipient($token);
}
