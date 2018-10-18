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
    public function getClientApisByParams($params){

        $conn = $this->getConnection();
        $query = "
            SELECT
                 tb1.id_client_api
                ,tb1.client_id
                ,tb1.client_secret
                ,tb1.name
                ,tb2.description

            FROM
                client_api tb1
                inner join redirect_uri tb2 on (tb1.id_application_third_part = tb2.id_application_third_part)
            WHERE
                tb1.client_secret = :client_secret
                AND
                tb1.client_id = :client_id
                AND
                tb2.description = :redirect_uri
        ";


        $query = $conn->prepare($query);

        $query->bindParam(':client_secret', $params['client_secret']);
        $query->bindParam(':client_id', $params['client_id']);
        $query->bindParam(':redirect_uri', $params['redirect_uri']);


        $query->execute();
        $status = $this->verifyBadExecute($query);
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $conn = null;

        if(!empty($line)){

            return $line;
        } else {
            return array();
        }
    }

    /**
     * Get all client_apis
     * @return array
     */
    public function getAllClientApis(){

        $conn = $this->getConnection();
        $query = "

            SELECT
                jsonb_object_agg(tb1.client_id, json)
            FROM (
                SELECT
                     sb1.name
                     ,sb1.id_application_third_part
                FROM
                    client_api sb1
                    inner join redirect_uri sb2 on (sb1.id_application_third_part = sb2.id_application_third_part)
            ) json
            inner join client_api tb1 on test.id_application_third_part = tb1.id_application_third_part

        ";


        $query = $conn->prepare($query);

        $query->execute();
        $status = $this->verifyBadExecute($query);
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $conn = null;

        if(!empty($line)){

            return $line;
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