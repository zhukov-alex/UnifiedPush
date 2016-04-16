<?php

namespace Zbox\UnifiedPush\Message;

class RecipientDeviceTest extends \PHPUnit_Framework_TestCase
{
    const VALID_GCM_IDENTIFIER = 'VWX4efa148e';

    /**
     * @dataProvider messageProvider
     *
     * @param string $message
     * @param bool $isValid
     */
    public function testSetIdentifier($message, $isValid)
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->getMockBuilder($message)
            ->setMethods(null)
            ->getMock();

        if (!$isValid) {
            $this->setExpectedException('Zbox\UnifiedPush\Exception\InvalidArgumentException');
        }

        $recipient = new RecipientDevice(
            self::VALID_GCM_IDENTIFIER,
            $messageMock
        );

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
            'GCM message' => array('Zbox\UnifiedPush\Message\Type\GCM', true),
            'APNS message' => array('Zbox\UnifiedPush\Message\Type\APNS', false)
        );
    }
}
