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
use Zbox\UnifiedPush\Message\Type\MPNSBase as MPNSMessage;
use Zbox\UnifiedPush\Message\Type\MPNSRaw;

/**
 * Class MPNS
 * @package Zbox\UnifiedPush\Notification\PayloadHandler
 */
class MPNS extends PayloadHandler
{
    /**
     * The maximum size allowed for MPNS message payload is 3K bytes
     */
    const PAYLOAD_MAX_LENGTH = 3072;

    /**
     * Notification delivery interval
     *
     * @var int
     */
    protected $delayInterval;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function isSupported(MessageInterface $message)
    {
        return $message instanceof MPNSMessage;
    }

    /**
     * @return \DOMDocument
     */
    public function createPayload()
    {
        /** @var MPNSMessage $message */
        $message = $this->message;

        $messageType = ucfirst($message->getMPNSType());

        $document      = new \DOMDocument("1.0", "utf-8");
        $baseElement  = $document->createElement("wp:Notification");
        $baseElement->setAttribute("xmlns:wp", "WPNotification");
        $document->appendChild($baseElement);

        $elementName = $message instanceof MPNSRaw ? 'root' : "wp:" . $messageType;
        $rootElement = $document->createElement($elementName);
        $baseElement->appendChild($rootElement);

        if ($message instanceof MPNSRaw) {
            $this->completeRawDocumentBody($document, $rootElement, $message);
        } else {
            $this->completeDocumentBody($document, $rootElement, $message);
        }

        return $document;
    }

    /**
     * @param \DOMDocument $payload
     * @return string
     */
    public function packPayload($payload)
    {
        return $payload->saveXML();
    }

    /**
     * @return array
     */
    public function getCustomNotificationData()
    {
        return
            array(
                'message_id'        => $this->message->getMessageIdentifier(),
                'delay_interval'    => $this->message->getDelayInterval(),
                'message_type'      => $this->message->getMPNSType()
            );
    }

    /**
     * @param \DOMDocument $document
     * @param \DOMElement $rootElement
     * @param MPNSMessage $message
     */
    protected function completeDocumentBody(
        \DOMDocument $document,
        \DOMElement $rootElement,
        MPNSMessage $message
    ) {
        foreach ($message->getPropertiesList() as $property)
        {
            $propertyName   = ucfirst($property->getName());
            $getterName     = 'get' . $propertyName;
            $value          = $this->$getterName();

            if ($value) {
                $name    = "wp:" . $propertyName;
                $element = $document->createElement($name, $value);
                $rootElement->appendChild($element);
            }
        }
    }

    /**
     * @param \DOMDocument $document
     * @param \DOMElement $rootElement
     * @param MPNSRaw $message
     */
    protected function completeRawDocumentBody(
        \DOMDocument $document,
        \DOMElement $rootElement,
        MPNSRaw $message
    ) {
        foreach ($message->getRawPayload() as $key => $value) {
            $element = $document->createElement($key, $value);
            $rootElement->appendChild($element);
        }
    }
}
