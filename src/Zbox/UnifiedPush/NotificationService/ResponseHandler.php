<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Exception\InvalidArgumentException;
use Zbox\UnifiedPush\NotificationService\APNS\ResponseFeedback;
use Zbox\UnifiedPush\Exception\InvalidRecipientException,
    Zbox\UnifiedPush\Exception\DispatchMessageException,
    Zbox\UnifiedPush\Exception\MalformedNotificationException;
use Psr\Log\LoggerAwareInterface,
    Psr\Log\LoggerInterface,
    Psr\Log\NullLogger;

class ResponseHandler implements LoggerAwareInterface
{
    /**
     * @var \SplObjectStorage
     */
    private $responseCollection;

    /**
     * @var \ArrayIterator
     */
    private $invalidRecipients;

    /**
     * @var \ArrayIterator
     */
    private $messageErrors;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->responseCollection   = new \SplObjectStorage();
        $this->invalidRecipients    = new \ArrayIterator();
        $this->messageErrors        = new \ArrayIterator();

        $this->setLogger(new NullLogger());
    }

    /**
     * @param string $messageIdentifier
     * @param ResponseInterface $response
     * @return $this
     */
    public function addIdentifiedResponse($messageIdentifier, ResponseInterface $response)
    {
        if (empty($messageIdentifier)) {
            throw new InvalidArgumentException('Identifier is required');
        }

        $this->responseCollection->attach($response, $messageIdentifier);
        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function addResponse(ResponseInterface $response)
    {
        $this->responseCollection->attach($response);
        return $this;
    }

    public function handleResponseCollection()
    {
        $responses = $this->responseCollection;

        $responses->rewind();

        while ($responses->valid()) {
            $response   = $responses->current();
            $messageId  = $responses->getInfo();
            $this->handleResponse($response, $messageId);
            $responses->next();
        }
    }

    /**
     * @param ResponseInterface $response
     * @param string $messageIdentifier
     */
    public function handleResponse(ResponseInterface $response, $messageIdentifier)
    {
        try {
            $response->processResponse();

            if ($response instanceof ResponseFeedback) {
                throw new InvalidRecipientException(null, $response->getRecipients());
            }
        } catch (InvalidRecipientException $e) {
            foreach ($e->getRecipientCollection() as $recipient) {
                $this->invalidRecipients->append($recipient);
            }

        } catch (DispatchMessageException $e) {
            $this->messageErrors->offsetSet($messageIdentifier, $e->getCode());

            $this->logger->warning(
                sprintf("Dispatch message warning with code %d  '%s'", $e->getCode(), $e->getMessage())
            );

        } catch (MalformedNotificationException $e) {
            $this->messageErrors->offsetSet($messageIdentifier, $e->getCode());

            $this->logger->error(
                sprintf("Malformed Notification error: %s", $e->getMessage())
            );
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getMessageErrors()
    {
        return $this->messageErrors;
    }

    /**
     * @return \ArrayIterator
     */
    public function getInvalidRecipients()
    {
        return $this->invalidRecipients;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
