<?php

namespace Zbox\UnifiedPush\Message;

/**
 * Class MessageCollectionTest
 * @package Zbox\UnifiedPush\Message
 */
class MessageCollectionTest extends \PHPUnit_Framework_TestCase
{
    const MESSAGE_APNS  = 'Zbox\UnifiedPush\Message\Type\APNS';
    const MESSAGE_GCM   = 'Zbox\UnifiedPush\Message\Type\GCM';

    public function testGetMessageCollection()
    {
        $apnsMock   = $this->getMock(self::MESSAGE_APNS);
        $gcmMock    = $this->getMock(self::MESSAGE_GCM);

        $messageCollection = new MessageCollection(
            array($apnsMock, $gcmMock)
        );

        $collection = $messageCollection->getMessageCollection();

        $this->assertContains($apnsMock, $collection);
        $this->assertContains($gcmMock, $collection);
    }
}
