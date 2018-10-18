<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CampuseroOAuth2\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity implements UserEntityInterface
{
    /**
     * Return the user's identifier.
     *
     * @return mixed
     */
    private $identifier;

    function __construct($identifier = null)
    {
    	$this->identifier = $identifier;
    }

    public function getIdentifier()
    {

        return $this->identifier;
    }
}
