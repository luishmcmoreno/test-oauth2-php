<?php

namespace CampuseroOAuth2\App\Service;

use CampuseroOAuth2\App\Service;
use CampuseroOAuth2\Library\MyLogger; 
use PDO;

class ClientApi extends Service
{

    public $log_file = "client_api_service.log";


    /**
     * Get all client_apis
     * @return array
     */
    public function getAllClientApis($grant_type_code){

        $conn = $this->getConnection();
        $query = "

            SELECT
                jsonb_object_agg(tb1.client_id, json)
            FROM 
                (
                    SELECT
                         sb1.name
                        ,sb1.id_application_third_part
                        ,sb2.description as redirect_uri
                        ,sb1.client_secret as secret
                        ,sb1.is_confidential
                    FROM
                        client_api sb1
                        inner join redirect_uri sb2 on (sb1.id_application_third_part = sb2.id_application_third_part)
                ) json
                inner join client_api tb1 on json.id_application_third_part = tb1.id_application_third_part
                inner join grant_type_client_api tb2 on (tb2.id_application_third_part = tb1.id_application_third_part)
                inner join grant_type tb3 on (tb3.id_grant_type = tb2.id_grant_type)
            WHERE
                tb3.code = :grant_type_code
        ";


        $query = $conn->prepare($query);
        $query->bindParam(':grant_type_code', $grant_type_code);
        $query->execute();
        $status = $this->verifyBadExecute($query);
        $line = $query->fetch(PDO::FETCH_ASSOC);
        $conn = null;

        if(!empty($line)){
            $data = json_decode($line['jsonb_object_agg'],true);
            foreach ($data as &$value) {
                $value['secret'] = password_hash($value['secret'], PASSWORD_BCRYPT);
            }
            return $data;
        } else {
            return array();
        }
    }

    /**
     * Register client_api
     * @return array
     */
    public function registerClientApi($params){

        $conn = $this->getConnection();
        $query = $conn->prepare("
            INSERT INTO client_api
                (client_id, name, client_secret)
            VALUES
                (:client_id, :name, :client_secret)
        ");


        $query->bindValue(':client_id', $this->generateRandomCharacter(32));
        $query->bindParam(':name', $params['name']);
        $query->bindValue(':client_secret', $this->generateRandomCharacter(6));
        $query->execute();

        $status = $this->verifyBadExecute($query);
        $id     = $conn->lastInsertId('id_application_third_part_seq');
        $conn   = null;

        if($status){
            return $id;
        } else{
            return $status;
        }
    }

     /**
     * Rollback post customer
     * @param $user
     * @return array
     */
    public function rollbackPostClientApi($id_client_api){

        $conn = $this->getConnection();

        $query = "
            DELETE FROM redirect_uri WHERE id_application_third_part = ".$id_client_api.";
            DELETE FROM client_api WHERE id_application_third_part = ".$id_client_api.";
        ";

        $conn->exec($query);

        $status = $this->verifyBadExecute($conn);
        $conn = null;
        
        return $status;
    }
}

?>