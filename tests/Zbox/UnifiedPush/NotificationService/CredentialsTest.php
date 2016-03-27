<?php

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsInterface;
use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsMapper;
use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\AuthToken;
use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\SSLCertificate;
use Zbox\UnifiedPush\Exception\DomainException;

class CredentialsTest extends \PHPUnit_Framework_TestCase
{
    const APNS_CREDENTIALS = 'APNS';
    const GCM_CREDENTIALS  = 'GCM';
    const MPNS_CREDENTIALS = 'MPNS';

    /**
     * @dataProvider credentialsProvider
     * @param string $serviceType
     * @param array $credentials
     * @param bool $isValid
     */
    public function testInitCredentials($serviceType, $credentials, $isValid)
    {
        if (!$isValid) {
            $this->setExpectedException('Zbox\UnifiedPush\Exception\InvalidArgumentException');
        }

        self::createCredentialsOfType($serviceType, $credentials);
    }

    /**
     * Credentials data provider
     */
    public static function credentialsProvider()
    {
        return array(
            'Valid Apns Test' => array(
                    self::APNS_CREDENTIALS,
                    array(
                        'certificate' => APNSServiceClientTest::getPathToCertificate(),
                        'certificatePassPhrase' => 'certificatePassPhrase'
                    ),
                    true
                ),
            'Invalid Apns Test' => array(
                    self::APNS_CREDENTIALS,
                    array(
                        'certificatePassPhrase' => 'certificatePassPhrase'
                    ),
                    false
            ),
            'Valid GCM Test' => array(
                self::GCM_CREDENTIALS,
                array(
                    'authToken' => 'testToken'
                ),
                true
            ),
            'Valid MPNS Test' => array(
                self::MPNS_CREDENTIALS,
                array(),
                true
            ),
            'Inalid MPNS Test' => array(
                self::MPNS_CREDENTIALS,
                array(
                    'authToken' => 'testToken'
                ),
                false
            )
        );
    }

    /**
     * @param string $serviceType
     * @param array $credentials
     * @return CredentialsInterface
     */
    public static function createCredentialsOfType($serviceType, $credentials)
    {
        $mapper = new CredentialsMapper();

        switch ($serviceType) {
            case self::APNS_CREDENTIALS:
            case self::MPNS_CREDENTIALS:
                return $mapper->mapCredentials(new SSLCertificate(), $credentials);
                break;

            case self::GCM_CREDENTIALS:
                return $mapper->mapCredentials(new AuthToken(), $credentials);
                break;

            default:
                throw new DomainException(sprintf("Unsupported service type '%'", $serviceType));
                break;
        }
    }
}
