<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush;

use Zbox\UnifiedPush\Message\MessageInterface,
    Zbox\UnifiedPush\Message\RecipientDevice;
use Zbox\UnifiedPush\Exception\DomainException,
    Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class Application
 * @package Zbox\UnifiedPush
 */
class Application
{
    const APPS_CREDENTIALS_FILENAME = 'applications_credentials.json';

    /**
     * @var array
     */
    private $config;

    /**
     * @var \ArrayObject
     */
    private $messages;

    /**
     * @var \ArrayIterator
     */
    private $refusedRecipients;

    /**
     * @param string $applicationName
     */
    public function __construct($applicationName)
    {
        $this->loadApplicationConfig($applicationName);
        $this->messages = new \ArrayObject();

        return $this;
    }

    /**
     * Returns path to application-based notification services credentials
     *
     * @return string
     */
    public function getCredentialsFilepath()
    {
        $filePath = __DIR__
            . DIRECTORY_SEPARATOR . 'Resources'
            . DIRECTORY_SEPARATOR . self::APPS_CREDENTIALS_FILENAME;

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(
                sprintf("Application credentials file '%s' not exists.", $filePath)
            );
        }

        return $filePath;
    }

    /**
     * Gets an iterator from an ArrayObject instance
     *
     * @return \ArrayIterator
     */
    public function getMessagesIterator()
    {
        return $this->messages->getIterator();
    }

    /**
     * Adds a message to collection
     *
     * @param MessageInterface $message
     * @return $this
     */
    public function addMessage(MessageInterface $message)
    {
        $messageId = $message->getMessageIdentifier();
        $this->messages->offsetSet($messageId, $message);
        return $this;
    }

    /**
     * Removes the given message from collection
     *
     * @param MessageInterface $message
     * @return $this
     */
    public function unsetMessage(MessageInterface $message)
    {
        $messageId = $message->getMessageIdentifier();
        $this->messages->offsetUnset($messageId);
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getRefusedRecipients()
    {
        return $this->refusedRecipients;
    }

    /**
     * Adds a refused recipient to collection
     *
     * @param string $serviceName
     * @param RecipientDevice $recipient
     * @return $this
     */
    public function addRefusedRecipient($serviceName, RecipientDevice $recipient)
    {
        if (!in_array($serviceName, $this->getInitializedServices())) {
            throw new DomainException(
                sprintf("Recipient of an unsupported service '%s'", $serviceName)
            );
        }

        $this->refusedRecipients->append($recipient);

        return $this;
    }

    /**
     * Returns the list of names of notification services available for the application
     *
     * @return array
     */
    public function getInitializedServices()
    {
        return array_keys($this->config);
    }

    /**
     * Returns credentials for notification service
     *
     * @param string $serviceName
     * @throws DomainException
     * @return string
     */
    public function getCredentialsByService($serviceName)
    {
        if (!in_array($serviceName, $this->getInitializedServices())) {
            throw new DomainException(
                sprintf("Credentials for service '%s' was not initialized", $serviceName)
            );
        }
        return $this->config[$serviceName];
    }

    /**
     * Load sender`s notification services credentials by application name
     *
     * @param string $applicationName
     * @return $this
     * @throws DomainException
     */
    public function loadApplicationConfig($applicationName)
    {
        $configFilePath = $this->getCredentialsFilepath();
        $applicationsConfig = json_decode(file_get_contents($configFilePath), true);

        if (!array_key_exists($applicationName, $applicationsConfig)) {
            throw new DomainException(
                sprintf("Application '%s' is not defined.", $applicationName)
            );
        }
        $this->config = $applicationsConfig[$applicationName];

        return $this;
    }
}
