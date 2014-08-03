<?php

namespace Zbox\UnifiedPush\Message;

use Zbox\UnifiedPush\Message\Type\MPNSRaw as MPNSRawMessage;

class RecipientDeviceTest extends \PHPUnit_Framework_TestCase
{
    const VALID_GCM_IDENTIFIER = 'VWX4efa148e';

    /**
     * @dataProvider messageProvider
     */
    public function testSetIdentifier($message, $isValid)
    {
        $recipient = $this->getMockBuilder('Zbox\UnifiedPush\Message\RecipientDevice')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        if (!$isValid) {
            $this->setExpectedException('Zbox\UnifiedPush\Exception\InvalidArgumentException');
        }

        $recipient->setIdentifier(self::VALID_GCM_IDENTIFIER, $message);

        $this->assertEquals(
            self::VALID_GCM_IDENTIFIER,
            $recipient->getIdentifier()
        );
    }

    /**
     * Message provider
     */
    public static function messageProvider()
    {
        return array(
            'GCM message' => array(new GCMMessage(), true),
            'APNS message' => array(new APNSMessage(), false),
            'MPNS message' => array(new MPNSRawMessage(), false)
        );
    }
}