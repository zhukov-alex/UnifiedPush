<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Utils;

/**
 * Class SocketClient
 * @package Zbox\UnifiedPush\Utils
 */
class SocketClient
{
    /**
     * @var int
     */
    private $addressType;

    /**
     * @var string
     */
    private $transport;

    /**
     * @var string
     */
    private $target;

    /**
     * @var int
     */
    private $targetPort;

    /**
     * @var array
     */
    private $contextOptions = array();

    /**
     * @var bool
     */
    private $blockingMode = true;

    /**
     * @var int
     */
    private $socketTimeout;

    /**
     * @var array
     */
    private $connectionFlags = array();

    /**
     * @var resource
     */
    private $streamResource;

    /**
     * @param $transport
     * @param $target
     * @param int $targetPort
     * @param int $addressType
     */
    public function __construct($transport, $target, $targetPort = null, $addressType = AF_INET)
    {
        $this
            ->setAddressType($addressType)
            ->setTransport($transport)
            ->setTarget($target)
            ->setTargetPort($targetPort)
        ;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        $isUnix = $this->getAddressType() === AF_UNIX;

        $address['transport'] = $this->getTransport() . ( $isUnix ? ':///' : '://' );
        $address['target']    = $this->getTarget();
        $address['port']      = ( $isUnix ? '' : ':' ) . $this->getTargetPort();

        return implode('', $address);
    }

    /**
     * @return int
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * @param int $addressType
     * @return $this
     */
    public function setAddressType($addressType)
    {
        if (!in_array($addressType,
            array(
                AF_INET,
                AF_INET6,
                AF_UNIX
            )
        )) {
            throw new \DomainException('Unsupported address type');
        }

        $this->addressType = $addressType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param string $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        if (!in_array($transport,stream_get_transports())) {
            throw new \DomainException(sprintf('Unsupported type of transport "%s"', $transport));
        }

        $this->transport = $transport;
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return int
     */
    public function getTargetPort()
    {
        return $this->targetPort;
    }

    /**
     * @param int $targetPort
     * @return $this
     */
    public function setTargetPort($targetPort)
    {
        if (
               $this->getAddressType() != AF_UNIX
            && !is_integer($targetPort)
        ) {
            throw new \InvalidArgumentException('Target port parameter must be an integer');
        }
        $this->targetPort = $targetPort;

        return $this;
    }

    /**
     * @param array $contextOptions
     * @return $this
     */
    public function setContextOptions(array $contextOptions)
    {
        $this->contextOptions = $contextOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getContextOptions()
    {
        return $this->contextOptions;
    }

    /**
     * @return boolean
     */
    public function isBlockingMode()
    {
        return $this->blockingMode;
    }

    /**
     * @param boolean $blockingMode
     * @return $this
     */
    public function setBlockingMode($blockingMode)
    {
        $this->blockingMode = $blockingMode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSocketTimeout()
    {
        if (!$this->socketTimeout) {
            return ini_get("default_socket_timeout");
        }

        return $this->socketTimeout;
    }

    /**
     * @param int $socketTimeout
     * @return $this
     */
    public function setSocketTimeout($socketTimeout)
    {
        if (!is_integer($socketTimeout)) {
            throw new \InvalidArgumentException('Socket timeout parameter must be an integer');
        }
        $this->socketTimeout = $socketTimeout;

        return $this;
    }

    /**
     * @return array
     */
    public function getConnectionFlags()
    {
        if (empty($this->connectionFlags)) {
            return STREAM_CLIENT_CONNECT;
        }

        return implode('|', $this->connectionFlags);
    }

    /**
     * @param int $connectionFlag
     * @return $this
     */
    public function addConnectionFlag($connectionFlag)
    {
        if (in_array($connectionFlag, $this->connectionFlags)) {
            return $this;
        }

        if (!in_array($connectionFlag, array(
                STREAM_CLIENT_CONNECT,
                STREAM_CLIENT_PERSISTENT,
                STREAM_CLIENT_PERSISTENT
            )
        ));
        $this->connectionFlags[] = $connectionFlag;

        return $this;
    }

    /**
     * @return $this
     */
    public function dropConnectionFlags()
    {
        $this->connectionFlags = array();
        return $this;
    }

    /**
     * @return resource
     */
    public function getStreamResource()
    {
        return $this->streamResource;
    }

    /**
     * @param resource $streamResource
     * @return $this
     */
    public function setStreamResource($streamResource)
    {
        if (!is_resource($streamResource)) {
            throw new \InvalidArgumentException('Stream resource parameter must be a resource');
        }
        $this->streamResource = $streamResource;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        return (bool) $this->streamResource;
    }

    /**
     * @return $this
     */
    public function connect()
    {
        $streamContext = stream_context_create(array(
            $this->getTransport() => $this->getContextOptions()
        ));

        $streamResource = stream_socket_client(
            $this->getAddress(),
            $errorCode,
            $errorMessage,
            $this->getSocketTimeout(),
            $this->getConnectionFlags(),
            $streamContext
        );

        if (!$streamResource) {
            throw new \RuntimeException(sprintf(
                'Unable to connect on socket. Error [%d]: %s',
                $errorCode,
                $errorMessage
            ));
        }

        stream_set_blocking($streamResource, $this->isBlockingMode());

        $this->setStreamResource($streamResource);

        return $this;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function write($data)
    {
        $streamResource = $this->getStreamResource();
        if (!is_resource($streamResource)) {
            throw new \UnexpectedValueException('Stream resource parameter must be a resource');
        }

        $dataLength = strlen($data);

        if ($dataLength !== fwrite($streamResource, $data, $dataLength)) {
            throw new \RuntimeException('Unable to write data to the stream');
        }

        return $this;
    }

    /**
     * @param int $length
     * @return string
     */
    public function read($length)
    {
        $streamResource = $this->getStreamResource();
        if (!is_resource($streamResource)) {
            throw new \UnexpectedValueException('Stream resource parameter must be a resource');
        }
        $streamsRead   = array($streamResource);
        $streamsWrite  = NULL;
        $streamsExcept = NULL;

        $hasDataToRead = stream_select(
            $streamsRead,
            $streamsWrite,
            $streamsExcept,
            0
        );

        if ($hasDataToRead) {
            return fread($streamResource, $length);
        }
        return false;
    }

    /**
     * @return $this
     */
    public function disconnect()
    {
        $streamResource = $this->getStreamResource();

        if (is_resource($streamResource)) {
            stream_socket_shutdown($streamResource, STREAM_SHUT_RDWR);
            $this->streamResource = null;
        }

        return $this;
    }
}
