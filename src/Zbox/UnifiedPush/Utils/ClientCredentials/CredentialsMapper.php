<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Utils\ClientCredentials;

use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\NullCredentials;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class CredentialsMapper
 * @package Zbox\UnifiedPush\Utils\ClientCredentials
 */
class CredentialsMapper
{
    /**
     * @param CredentialsInterface $credentials
     * @param array $params
     * @return CredentialsInterface
     */
    public function mapCredentials(CredentialsInterface $credentials, array $params)
    {
        if (empty($params)) {
            return new NullCredentials();
        }

        $attributes = $this->getCredentialAttributes($credentials);

        $this->validateAttributes($attributes, array_keys($params));

        foreach ($attributes as $attribute) {
            $setterName = sprintf('set%s', ucfirst($attribute));
            $credentials->$setterName($params[$attribute]);
        }

        return $credentials;
    }

    /**
     * @param CredentialsInterface $credentials
     * @return array
     */
    protected function getCredentialAttributes(CredentialsInterface $credentials)
    {
        $reflection = new \ReflectionObject($credentials);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);

        $attributesList = array();

        foreach ($properties as $property) {
            $attributesList[] = $property->getName();
        }

        return $attributesList;
    }

    /**
     * @param array $required
     * @param array $acquired
     */
    protected function validateAttributes($required, $acquired)
    {
        $missingAttributes = array_diff($required, $acquired);

        if (!empty($missingAttributes)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Mandatory attribute missing: %s',
                    implode(', ', $missingAttributes)
                )
            );
        }
    }
}
