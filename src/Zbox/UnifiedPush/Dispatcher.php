<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush;

use Zbox\UnifiedPush\Message\MessageInterface;
use Zbox\UnifiedPush\Message\MessageCollection;
use Zbox\UnifiedPush\Notification\Notification;
use Zbox\UnifiedPush\Notification\NotificationBuilder;
use Zbox\UnifiedPush\NotificationService\NotificationServices,
    Zbox\UnifiedPush\NotificationService\ServiceClientInterface,
    Zbox\UnifiedPush\NotificationService\ServiceClientFactory,
    Zbox\UnifiedPush\NotificationService\ResponseHandler;
use Zbox\UnifiedPush\Exception\ClientException,
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
     * @var array
     */
    private $connectionPool;

    /**
     * @var ServiceClientFactory
     */
    private $clientFactory;

    /**
     * @var NotificationBuilder
     */
    private $notificationBuilder;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ServiceClientFactory $clientFactory
     * @param NotificationBuilder $notificationBuilder
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        ServiceClientFactory $clientFactory,
        NotificationBuilder $notificationBuilder,
        ResponseHandler $responseHandler
    ){
        $this->clientFactory        = $clientFactory;
        $this->notificationBuilder  = $notificationBuilder;
        $this->responseHandler      = $responseHandler;

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
        $connection = $this->clientFactory->createServiceClient($serviceName);
        $this->connectionPool[$serviceName] = $connection;

        return $this;
    }

    /**
     * Creates a feedback service connection
     *
     * @param string $serviceName
     * @return ServiceClientInterface
     */
    public function initFeedbackConnection($serviceName)
    {
        return $this->clientFactory->createServiceClient($serviceName, true);
    }

    /**
     * @return ResponseHandler
     */
    public function getResponseHandler()
    {
        return $this->responseHandler;
    }

    /**
     * Build notification and send it to notification service
     *
     * @param MessageInterface $message
     * @return $this
     */
    public function dispatch(MessageInterface $message)
    {
        $builder = $this->notificationBuilder;

        $notifications = $builder->buildNotifications($message);

        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            $this->sendNotification($notification);
        }

        return $this;
    }

    /**
     * Tries to dispatch all messages to notification service
     *
     * @param MessageCollection $messages
     * @return $this
     */
    public function dispatchAll(MessageCollection $messages)
    {
        $collection = $messages->getMessageCollection();

        while ($collection->valid()) {
            $message = $collection->current();
            $this->dispatch($message);
            $collection->next();
        }

        return $this;
    }

    /**
     * Tries to connect and send a notification
     *
     * @param Notification $notification
     * @return bool
     */
    public function sendNotification(Notification $notification)
    {
        try {
            $connection = $this->getConnection($notification->getType());
            $connection->setNotification($notification);

            $this->logger->info(
                sprintf(
                    "Dispatching notification id: %s",
                    $notification->getIdentifier()
                )
            );

            $this
                ->responseHandler
                ->addIdentifiedResponse(
                    $notification->getIdentifier(),
                    $connection->sendRequest()
                );

            return true;

        } catch (ClientException $e) {
            $this->logger->error(
                sprintf("Client connection error: %s", $e->getMessage())
            );

        } catch (RuntimeException $e) {
            $this->logger->error(
                sprintf("Runtime error: %s", $e->getMessage())
            );

        } catch (\Exception $e) {
            $this->logger->error(
                sprintf("Error occurs: %s", $e->getMessage())
            );
        }

        return false;
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

            $connection = $this->initFeedbackConnection($serviceName);

            $this
                ->responseHandler
                ->addResponse(
                    $connection->sendRequest()
                );

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
