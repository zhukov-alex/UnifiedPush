<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush;

use Zbox\UnifiedPush\Message\MessageInterface,
    Zbox\UnifiedPush\Notification\NotificationBuilder;
use Zbox\UnifiedPush\NotificationService\NotificationServices,
    Zbox\UnifiedPush\NotificationService\ServiceClientInterface,
    Zbox\UnifiedPush\NotificationService\ServiceClientFactory;
use Zbox\UnifiedPush\Exception\InvalidRecipientException,
    Zbox\UnifiedPush\Exception\DispatchMessageException,
    Zbox\UnifiedPush\Exception\MalformedNotificationException,
    Zbox\UnifiedPush\Exception\ClientException,
    Zbox\UnifiedPush\Exception\RuntimeException;
use Psr\Log\LoggerAwareInterface,
    Psr\Log\LoggerInterface,
    Psr\Log\NullLogger;

/**
 * Class Dispatcher
 * @package Zbox\UnifiedPush
 */
class Dispatcher implements LoggerAwareInterface
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var NotificationBuilder
     */
    private $notificationBuilder;

    /**
     * @var array
     */
    private $connectionPool;

    /**
     * @var ServiceClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Application $application
     * @param ServiceClientFactory $clientFactory
     * @param NotificationBuilder $notificationBuilder
     */
    public function __construct(
        Application $application,
        ServiceClientFactory $clientFactory,
        NotificationBuilder $notificationBuilder
    ){
        $this->application    = $application;
        $this->clientFactory  = $clientFactory;
        $this->notificationBuilder = $notificationBuilder;

        $this->setLogger(new NullLogger());
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

    /**
     * @param bool $isDevelopment
     * @return $this
     */
    public function setDevelopmentMode($isDevelopment)
    {
        $this->clientFactory->setDevelopmentMode($isDevelopment);
        return $this;
    }

    /**
     * Returns credentials for notification service
     *
     * @param string $serviceName
     * @return array
     */
    private function getServiceCredentials($serviceName)
    {
        return $this->application->getCredentialsByService($serviceName);
    }

    /**
     * Gets a service client connection by service name
     *
     * @param string $serviceName
     * @return ServiceClientInterface
     */
    public function getConnection($serviceName)
    {
        if (empty($this->connectionPool[$serviceName])) {
            $this->initConnection($serviceName);
        }

        return $this->connectionPool[$serviceName];
    }

    /**
     * Initialize service client connection by service name
     *
     * @param string $serviceName
     * @return $this
     */
    public function initConnection($serviceName)
    {
        $credentials  = $this->getServiceCredentials($serviceName);
        $connection   = $this->clientFactory->createServiceClient($serviceName, $credentials);
        $this->connectionPool[$serviceName] = $connection;

        return $this;
    }

    /**
     * Creates a feedback service connection
     *
     * @param string $serviceName
     * @return ServiceClientInterface
     */
    private function createFeedbackConnection($serviceName)
    {
        $credentials  = $this->getServiceCredentials($serviceName);
        return $this->clientFactory->createServiceClient($serviceName, $credentials, true);
    }

    /**
     * Tries to connect and send a message to notification service
     *
     * @param MessageInterface $message
     * @return bool
     * @throws Zbox\UnifiedPush\Exception\InvalidRecipientException
     * @throws Zbox\UnifiedPush\Exception\DispatchMessageException
     * @throws Zbox\UnifiedPush\Exception\MalformedNotificationException
     */
    private function sendMessage(MessageInterface $message)
    {
        $this->logger->info(
            sprintf("Sending message id '%s'", $message->getMessageIdentifier())
        );

        $builder = $this->notificationBuilder;

        $builder->buildNotifications($message);

        while ($notification = $builder->getNotification()) {
            try {
                $connection = $this->getConnection($message->getMessageType());
                $connection->setNotification($notification);
                $connection->sendRequest();

            } catch (InvalidRecipientException $e) {
                while ($recipient = $e->getRecipientDevice()) {
                    $this->application->addInvalidRecipient($message->getMessageType(), $recipient);
                }

            } catch (DispatchMessageException $e) {
                $this->logger->warning(
                    sprintf("Dispatch message warning with code %d  '%s'", $e->getCode(), $e->getMessage())
                );

            } catch (MalformedNotificationException $e) {
                $this->logger->error(
                    sprintf("Malformed Notification error: %s", $e->getMessage())
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Tries to dispatch all messages to notification service
     *
     * @return bool
     * @throws Zbox\UnifiedPush\Exception\ClientException
     * @throws Zbox\UnifiedPush\Exception\RuntimeException
     * @throws \Exception
     */
    public function dispatch()
    {
        $messages = $this->application->getMessagesIterator();

        try {
            while ($messages->valid()) {
                $message = $messages->current();
                if ($this->sendMessage($message)) {
                    $messages->offsetUnset($message->getMessageIdentifier());
                }
                $messages->next();
            }
        } catch (ClientException $e) {
            $this->logger->error(
                sprintf("Client connection error: %s", $e->getMessage())
            );
            return;

        } catch (RuntimeException $e) {
            $this->logger->error(
                sprintf("Runtime error: %s", $e->getMessage())
            );
            return;

        } catch (\Exception $e) {
            $this->logger->error(
                sprintf("Error occurs: %s", $e->getMessage())
            );
            return;
        }
    }

    /**
     * Tries to connect and load feedback data
     *
     * @return $this
     * @throws RuntimeException
     * @throws \Exception
     */
    public function loadFeedback()
    {
        $serviceName = NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE;

        try {
            $this->logger->info(sprintf("Querying the feedback service '%s'", $serviceName));

            $connection = $this->createFeedbackConnection($serviceName);
            $invalidRecipients = $connection->sendRequest();

            while ($invalidRecipients->valid()) {
                $recipient = $invalidRecipients->current();
                $this->application->addInvalidRecipient($serviceName, $recipient);
                $invalidRecipients->next();
            }

        } catch (RuntimeException $e) {
            $this->logger->error(
                sprintf("Runtime error while acquiring feedback: %s", $e->getMessage())
            );

        } catch (\Exception $e) {
            $this->logger->error(
                sprintf("Error occurs while acquiring feedback: %s", $e->getMessage())
            );
        }

        return $this;
    }
}
