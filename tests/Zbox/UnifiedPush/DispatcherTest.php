<?php

namespace Zbox\UnifiedPush;

use Zbox\UnifiedPush\Notification\Notification;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDevelopmentMode()
    {
        $clientFactoryStub = $this->getClientFactoryMock();

        $clientFactoryStub
            ->expects($this->once())
            ->method('setDevelopmentMode')
            ->with(true)
        ;

        $dispatcher = $this->getDispatcher($clientFactoryStub);
        $dispatcher->setDevelopmentMode(true);
    }

    public function testGetConnection()
    {
        $clientFactory = $this->getClientFactoryMock();

        $clientFactory
            ->expects($this->once())
            ->method('createServiceClient')
            ->with('test')
        ;

        $dispatcher = $this->getDispatcher($clientFactory);
        $dispatcher->getConnection('test');
    }

    public function testDispatch()
    {
        $message = $this->getMock('\Zbox\UnifiedPush\Message\MessageInterface');

        $notificationBuilder = $this->getMock('\Zbox\UnifiedPush\Notification\NotificationBuilder');

        $notificationBuilder
            ->expects($this->once())
            ->method('buildNotifications')
            ->with($message)
            ->will($this->returnValue(new \ArrayIterator()))
        ;

        $dispatcher = $this->getDispatcher(null, $notificationBuilder);
        $dispatcher->dispatch($message);
    }

    public function testSendNotification()
    {
        $responseHandler = $this->getMock('\Zbox\UnifiedPush\NotificationService\ResponseHandler');

        $responseHandler
            ->expects($this->once())
            ->method('addIdentifiedResponse')
            ->with('testId', $this->getResponseMock())
        ;

        $notification  = $this->getNotificationStub();
        $client = $this->getServiceClientStub($notification);

        $clientFactory = $this->getClientFactoryMock();

        $clientFactory
            ->expects($this->once())
            ->method('createServiceClient')
            ->with('testType')
            ->will($this->returnValue($client))
        ;

        $dispatcher = $this->getDispatcher($clientFactory, null, $responseHandler);
        $this->assertTrue($dispatcher->sendNotification($notification));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Notification
     */
    protected function getNotificationStub()
    {
        $notification = $this->getMock('Zbox\UnifiedPush\Notification\Notification');

        $notification
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('testType'))
        ;

        $notification
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('testId'))
        ;

        return $notification;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NotificationService\ServiceClientFactory
     */
    protected function getClientFactoryMock()
    {
        return
            $this
                ->getMockBuilder('\Zbox\UnifiedPush\NotificationService\ServiceClientFactory')
                ->disableOriginalConstructor()
                ->getMock()
        ;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NotificationService\ResponseInterface
     */
    protected function getResponseMock()
    {
        return $this->getMock('Zbox\UnifiedPush\NotificationService\ResponseInterface');
    }

    /**
     * @param Notification $notification
     * @return \PHPUnit_Framework_MockObject_MockObject|NotificationService\ServiceClientInterface
     */
    protected function getServiceClientStub(Notification $notification)
    {
        $client =
            $this
                ->getMockBuilder('\Zbox\UnifiedPush\NotificationService\ServiceClientInterface')
                ->disableOriginalConstructor()
                ->getMock()
        ;

        $client
            ->expects($this->once())
            ->method('setNotification')
            ->with($notification)
            ->will($this->returnSelf())
        ;

        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($this->getResponseMock()))
        ;

        return $client;
    }

    protected function getDispatcher(
        $clientFactory = null,
        $notificationBuilder = null,
        $responseHandler = null
    ) {
        if (is_null($clientFactory)) {
            $clientFactory = $this->getClientFactoryMock();
        }

        return
            new Dispatcher(
                $clientFactory,
                $notificationBuilder ? : $this->getMock('\Zbox\UnifiedPush\Notification\NotificationBuilder'),
                $responseHandler ? : $this->getMock('\Zbox\UnifiedPush\NotificationService\ResponseHandler')
            );
    }
}
