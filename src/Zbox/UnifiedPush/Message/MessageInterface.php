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

    public function getMaxRecipientsPerMessage();

    public function getRecipientDeviceCollection();

    public function setRecipientDeviceCollection(\ArrayIterator $collection);

    public function addRecipient($deviceIdentifier);

    public function addRecipientIdentifiers(\ArrayIterator $collection);

    public function validateRecipient($token);
}
