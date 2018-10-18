<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CampuseroOAuth2\Repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use CampuseroOAuth2\Entities\AuthCodeEntity;
use CampuseroOAuth2\App\Service;
use CampuseroOAuth2\Library\MyLogger; 
use PDO;

class AuthCodeRepository extends Service implements AuthCodeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)    
    {
        $user_id = $authCodeEntity->getUserIdentifier();
        $client_id = $authCodeEntity->getClient()->getIdentifier();
        $expire_code = date("Y-m-d H:i:s", $authCodeEntity->getExpiryDateTime()->getTimestamp());
        $code_id = $authCodeEntity->getIdentifier();

        $conn = $this->getConnection();
        $query = $conn->prepare("
            INSERT INTO auth_code
                (id_application_third_part, user_id, expire_code, code_id)
            VALUES
                ((select id_application_third_part from client_api where client_id = :client_id), :user_id, :expire_code, :code_id)
        ");

        $query->bindValue(':client_id', $client_id);
        $query->bindParam(':user_id', $user_id);
        $query->bindValue(':expire_code', $expire_code);
        $query->bindValue(':code_id', $code_id);
        $query->execute();

        $status = $this->verifyBadExecute($query);
        //$id     = $conn->lastInsertId('id_application_third_part_seq');
        $conn   = null;
        // Some logic to persist the auth code to a database
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        // Some logic to revoke the auth code in a database
        $conn = $this->getConnection();
        $query = $conn->prepare("
            UPDATE 
                auth_code
            SET 
                is_revoked = true
            WHERE 
                code_id = :code_id
        ");

        $query->bindValue(':code_id', $codeId);
        $query->execute();

        $status = $this->verifyBadExecute($query);
        //$id     = $conn->lastInsertId('id_application_third_part_seq');
        $conn   = null;

    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $conn = $this->getConnection();

        $query = $conn->prepare("
            SELECT
                 tb1.is_revoked
            FROM
                auth_code tb1
            WHERE 
                tb1.code_id = :code_id
                
        ");

        $query->bindValue(':code_id', $codeId);
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
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }
}
