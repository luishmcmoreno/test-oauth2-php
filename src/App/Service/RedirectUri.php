<?php

namespace CampuseroOAuth2\App\Service;

use CampuseroOAuth2\App\Service;
use CampuseroOAuth2\Library\MyLogger; 
use PDO;


class RedirectUri extends Service
{

    public $log_file = "redirect_uri_service.log";


    /**
     * Register event supplier
     * @return array
     */
    public function registerRedirectUri($id_client, $params){

        $conn = $this->getConnection();

        $response = array(
             'status'  => false
            ,'redirect_uri' => array()
        );

        $conn->beginTransaction();

        $query = $conn->prepare("
            INSERT INTO redirect_uri
                (id_application_third_part, description)
            VALUES 
                (:id_client, :description)
        ");

        $status = array();
      
        foreach ($params as $key => $value) {
            $query->bindParam(':id_client', $id_client);
            $query->bindParam(':description', $value['http_address']);
            $query->execute();
            $status[]  = $this->verifyBadExecute($query);            
        }

        if(in_array(false, $status)){
     
            $conn->rollBack();
            $response['status'] = false;
        } else{

            $conn->commit();
            $response['status'] = true;
        }


        $conn = null;
        return $response['status'] ;
    }



}