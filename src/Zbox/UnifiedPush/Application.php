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
     * @var string
     */
    private $credentialsFilePath;

    /**
     * @var string
     */
    private $applicationName;

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
    private $invalidRecipients;

    /**
     * @param string $applicationName
     */
    public function __construct($applicationName)
    {
        $this->setApplicationName($applicationName);
        $this->messages = new \ArrayObject();

        return $this;
    }

    /**
     * Returns path to application-based notification services credentials
     *
     * @return string
     */
    public function getCredentialsFilePath()
    {
        if (!file_exists($this->credentialsFilePath)) {
            $credentialsFilename = sprintf('%s.dist', static::APPS_CREDENTIALS_FILENAME);

            throw new InvalidArgumentException(
                sprintf(
                    "Application credentials file '%s' not exists. See example %s",
                    $this->credentialsFilePath,
                    $credentialsFilename
                )
            );
        }
        return $this->credentialsFilePath;
    }

    /**
     * @param string $filePath
     * @return $this
     */
    public function setCredentialsFilePath($filePath)
    {
        $this->credentialsFilePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * @param string $applicationName
     * @return $this
     */
    public function setApplicationName($applicationName)
    {
        $this->applicationName = $applicationName;
        return $this;
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
    public function getInvalidRecipients()
    {
        return $this->invalidRecipients;
    }

    /**
     * Adds a invalid recipient to collection
     *
     * @param string $serviceName
     * @param RecipientDevice|string $recipient
     * @return $this
     */
    public function addInvalidRecipient($serviceName, $recipient)
    {
        if (!in_array($serviceName, $this->getInitializedServices())) {
            throw new DomainException(
                sprintf("Recipient of an unsupported service '%s'", $serviceName)
            );
        }

        if (is_string($recipient)) {
            $messageClassName = 'Zbox\UnifiedPush\Message\Type\\' . $serviceName;
            $recipient        = new RecipientDevice($recipient, $messageClassName());

            $recipient->setIdentifierStatus(RecipientDevice::DEVICE_NOT_REGISTERED);
        }

        $this->invalidRecipients->append($recipient);

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
     * @return array
     */
    public function getCredentialsByService($serviceName)
    {
        if ($this->getApplicationName() && empty($this->config)) {
            $this->loadApplicationConfig();
        }

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
     * @return $this
     * @throws DomainException
     */
    public function loadApplicationConfig()
    {
        $configFilePath = $this->getCredentialsFilepath();
        $applicationsConfig = json_decode(file_get_contents($configFilePath), true);

        $applicationName = $this->getApplicationName();

        if (!array_key_exists($applicationName, $applicationsConfig)) {
            throw new DomainException(
                sprintf("Application '%s' is not defined.", $applicationName)
            );
        }
        $this->config = $applicationsConfig[$applicationName];

        return $this;
    }
}
