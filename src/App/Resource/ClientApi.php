<?php

namespace CampuseroOAuth2\App\Resource;

use CampuseroOAuth2\App\Resource;
use CampuseroOAuth2\App\Service\ClientApi as ClientApiService;
use CampuseroOAuth2\App\Resource\RedirectUri as RedirectUriResource;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientApi extends Resource
{
    /**
     * @var \App\Service\ClientApi
     */
    private $clientApiService;
    private $log_file = "client_api_resource.log";

    /**
     * Get ClientApi service
     */
    public function init(){

        $this->setClientApiService(new ClientApiService());
    }

    /**
     * get ClientApis
     */
    public function getClientApis(){

        $env    = $this->getSlim()->environment('user');
        $req    = $this->getSlim()->request();
        $params = array(
            'name'              => $req->get('name'),
            'limit'             => $req->get('limit'),
            'offset'            => $req->get('offset')
        );

        $client_api['total']   = $this->getClientApiService()->getCountClientApis($params);
        $client_api['client_api'] = $this->getClientApiService()->getClientApis($params);

        self::response(self::STATUS_OK, $ClientApi);
    }

    /**
     * Register ClientApi
     */
    public function registerClientApi(ServerRequestInterface $request, ResponseInterface $response){

        $redirectUriResource = new RedirectUriResource();

        $obj_params = $this->underscoreKeys($request->getParsedBody());
        $error = $this->checkParamsCreate($obj_params);
        if(!empty($error) && !empty($error['http_code'])){
            return $error;
        }

        $error = $redirectUriResource->checkParamsCreate($obj_params);
        if(!empty($error) && !empty($error['http_code'])){
            return $error;
        }
        $id_client_api = $this->getClientApiService()->registerClientApi($obj_params);
        if(!$id_client_api){
            return array('http_code'=> self::STATUS_INTERNAL_SERVER_ERROR, 'response'=>array(
                'status'   => "InternalServerError",
                'message'  => "Internal server error on register ClientApi."
                ));            
        }

        $id_redirect_uri = $redirectUriResource->getRedirectUriService()->registerRedirectUri($id_client_api, $obj_params['redirect_uri']);
        if(!$id_redirect_uri){
            $this->getClientApiService()->rollbackPostClientApi();
            return array('http_code'=> self::STATUS_INTERNAL_SERVER_ERROR, 'response'=>array(
                'status'   => "InternalServerError",
                'message'  => "Internal server error on register RedirectUri."
                ));            
        }

        return array('http_code'=> self::STATUS_CREATED, 'response'=> array('id_client_api' => $id_client_api)); 
    }

    /**
     * Validade create params
     */
    public function checkParamsCreate($obj_params){
        if (
               empty($obj_params['name'])
            || empty($obj_params['redirect_uri'])
        ){

            return array('http_code'=> self::STATUS_BAD_REQUEST, 'response'=>  array(
                'status'   => "MissingFields",
                'message'  => "Missing fields",
                'required' => array(
                     'name'
                    ,'redirect_uri'
                )
            ));
        }
        return true;
    }


    /**
     * @return \App\Service\ClientApi
     */
    public function getClientApiService(){

        return $this->clientApiService;
    }

    /**
     * @param \App\Service\AUthorize $clientApiService
     */
    public function setClientApiService($clientApiService){

        $this->clientApiService = $clientApiService;
    }

}