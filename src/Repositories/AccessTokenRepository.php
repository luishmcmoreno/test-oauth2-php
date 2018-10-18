<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CampuseroOAuth2\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use CampuseroOAuth2\Entities\AccessTokenEntity;
use CampuseroOAuth2\App\Service;
use CampuseroOAuth2\Library\MyLogger; 
use PDO;

class AccessTokenRepository extends Service implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {

        // Some logic here to save the access token to a database
        $user_id = $accessTokenEntity->getUserIdentifier();
        $client_id = $accessTokenEntity->getClient()->getIdentifier();
        $expire_token = date("Y-m-d H:i:s", $accessTokenEntity->getExpiryDateTime()->getTimestamp());
        $token_id = $accessTokenEntity->getIdentifier();

        $conn = $this->getConnection();
        $query = $conn->prepare("
            INSERT INTO access_token
                (id_application_third_part, user_id, expire_token, token_id)
            VALUES
                ((select id_application_third_part from client_api where client_id = :client_id), :user_id, :expire_token, :token_id)
        ");

        $query->bindValue(':client_id', $client_id);
        $query->bindParam(':user_id', $user_id);
        $query->bindValue(':expire_token', $expire_token);
        $query->bindValue(':token_id', $token_id);
        $query->execute();

        $status = $this->verifyBadExecute($query);
        //$id     = $conn->lastInsertId('id_application_third_part_seq');
        $conn   = null;
        //levantar exceções quando algo der errado!

                
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        // Some logic here to revoke the access token
        //verificar para revogar os tokens gerados por grants diferentes
        $conn = $this->getConnection();
        $query = $conn->prepare("
            UPDATE 
                access_token tb1
            SET 
                is_revoked = true
            WHERE 
                token_id = :token_id
        ");

        $query->bindValue(':token_id', $tokenId);
        $query->execute();

        $status = $this->verifyBadExecute($query);
        //$id     = $conn->lastInsertId('id_application_third_part_seq');
        $conn   = null;
        //levantar exceções quando algo der errado!
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {

        $conn = $this->getConnection();

        $query = $conn->prepare("
            SELECT
                 tb1.is_revoked
            FROM
                access_token tb1
            WHERE 
                tb1.token_id = :token_id
                
        ");

        $query->bindValue(':token_id', $tokenId);
        $query->execute();
        $line = $query->fetch(PDO::FETCH_ASSOC);
        $conn = null;
        if(!empty($line)){
            return $line['is_revoked'];
        } else {
            //retorna true se não encontrar esse token id
            return true;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function isFirtsPartAccessToken($tokenId)
    {

        $conn = $this->getConnection();

        $query = $conn->prepare("
            SELECT
                tb1.id_access_token
            FROM
                access_token tb1
                inner join client_api tb2 on (tb1.id_application_third_part = tb2.id_application_third_part)
                inner join grant_type_client_api tb3 on (tb2.id_application_third_part = tb3.id_application_third_part)
                inner join grant_type tb4 on (tb4.id_grant_type = tb3.id_grant_type)
            WHERE 
                tb1.token_id = :token_id
                AND
                tb4.code = 'password'

                
        ");

        $query->bindValue(':token_id', $tokenId);
        $query->execute();
        $line = $query->fetch(PDO::FETCH_ASSOC);
        $conn = null;
        if(!empty($line)){
            return true;
        } else {
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }
}
