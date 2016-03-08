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

    public function getRecipientDevice();

    public function getRecipientCollection();

    public function setRecipientCollection(\ArrayIterator $collection);

    public function addRecipient($deviceIdentifier);

    public function addRecipientIdentifiers(\ArrayIterator $collection);

    public function createPayload();

    public function packPayload($payload);

    public function validateRecipient($token);
}
