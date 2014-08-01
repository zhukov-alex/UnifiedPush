<?php

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\NotificationService\APNS\Credentials as APNSCredentials;
use Zbox\UnifiedPush\NotificationService\GCM\Credentials as GCMCredentials;
use Zbox\UnifiedPush\Exception\DomainException;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

class CredentialsTest extends \PHPUnit_Framework_TestCase
{
    const APNS_CREDENTIALS = 'APNS';
    const GCM_CREDENTIALS  = 'GCM';

    /**
     * @dataProvider credentialsProvider
     */
    public function testInitCredentials($serviceType, $credentials, $isValid)
    {
        if (!$isValid) {
            $this->setExpectedException('Zbox\UnifiedPush\Exception\InvalidArgumentException');
        }

        $credentials = $this->createCredentialsOfType($serviceType, $credentials);
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
            )
        );
    }

    /**
     * @param string $serviceType
     * @param array $credentials
     * @return CredentialsInterface
     */
    public function createCredentialsOfType($serviceType, $credentials)
    {
        switch ($serviceType) {
            case self::APNS_CREDENTIALS:
                return new APNSCredentials($credentials);
                break;

            case self::GCM_CREDENTIALS:
                return new GCMCredentials($credentials);
                break;

            default:
                throw new DomainException(sprintf("Unsupported service type '%'", $serviceType));
                break;
        }
    }
}
