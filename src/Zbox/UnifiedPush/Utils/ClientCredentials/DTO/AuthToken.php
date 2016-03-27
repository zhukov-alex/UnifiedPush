<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Utils\ClientCredentials\DTO;

use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsInterface;

/**
 * Class AuthToken
 * @package Zbox\UnifiedPush\Utils\ClientCredentials
 */
class AuthToken implements CredentialsInterface
{
    /**
     * An API key
     *
     * @var string
     */
    private $authToken;

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     * @return $this
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;

        return $this;
    }
}
