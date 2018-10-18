<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CampuseroOAuth2\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use CampuseroOAuth2\Entities\RefreshTokenEntity;
use CampuseroOAuth2\App\Service;
use CampuseroOAuth2\Library\MyLogger; 
use PDO;

class RefreshTokenRepository extends Service implements RefreshTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {        
        // Some logic to persist the refresh token in a database
        $user_id = $refreshTokenEntity->getAccessToken()->getUserIdentifier();
        $client_id = $refreshTokenEntity->getAccessToken()->getClient()->getIdentifier();
        $expire_token = date("Y-m-d H:i:s", $refreshTokenEntity->getExpiryDateTime()->getTimestamp());
        $token_id = $refreshTokenEntity->getIdentifier();

        $conn = $this->getConnection();
        $query = $conn->prepare("
            INSERT INTO refresh_token
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
    public function revokeRefreshToken($tokenId)
    {
        // Some logic to revoke the refresh token in a database
        //verificar para revogar os tokens gerados por grants diferentes
        $conn = $this->getConnection();
        $query = $conn->prepare("
            UPDATE 
                refresh_token
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
    public function isRefreshTokenRevoked($tokenId)
    {
        //return false; // The refresh token has not been revoked
        $conn = $this->getConnection();

        $query = $conn->prepare("
            SELECT
                 tb1.is_revoked
            FROM
                refresh_token tb1
            WHERE 
                token_id = :token_id
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
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }
}
