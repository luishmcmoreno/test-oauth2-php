<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CampuseroOAuth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use CampuseroOAuth2\Entities\UserEntity;
use CampuseroOAuth2\App\Service;
use \PDO;

class UserRepository extends Service implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $email,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        
        $conn = $this->getConnection();

        $query = $conn->prepare("
            SELECT
                 tb1.email
                ,tb1.password
                ,tb1.id

            FROM
                profile_userprofile tb1
            WHERE
                tb1.email = :email
                and
                tb1.password = md5(:password)
        ");

        $query->bindParam(':email', $email);
        $query->bindParam(':password', $password);
        $query->execute();
        $line = $query->fetch(PDO::FETCH_ASSOC);
        $conn = null;
        if(!empty($line)){
            return new UserEntity($line['id']);
        } else {
            return ;
        }
    }

}
