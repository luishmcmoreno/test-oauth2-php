<?php

namespace CampuseroOAuth2\App\Resource;

use CampuseroOAuth2\App\Resource;
use CampuseroOAuth2\App\Service\RedirectUri as RedirectUriService;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class RedirectUri extends Resource
{
    /**
     * @var \App\Service\RedirectUri
     */
    private $redirectUriService;
    private $log_file = "redirect_uri_resource.log";

    /**
     * Get redirect_uri service
     */
    public function init(){

        $this->setRedirectUriService(new RedirectUriService());
    }


    /**
     * Validade create params
     */
    public function checkParamsCreate($obj_params){

        if(!isset($obj_params['redirect_uri'])){
            return array('http_code'=> self::STATUS_BAD_REQUEST, 
            	'response'=> array(
	                'status'   => "MissingStructure",
	                'message'  => "Missing structure.",
	                'required' => array('redirect_uri')
	                )
            	);
            
        }

        if(!is_array($obj_params['redirect_uri'])){
            return array('http_code'=> self::STATUS_BAD_REQUEST, 
            	'response'=> array(
	                'status'   => "InvalidStructure",
	                'message'  => "Invalid structure",
	                'required' => array('redirectUri')
	                )
            );
            
        }

        foreach ($obj_params['redirect_uri'] as $key => $value) {
            if(empty($value['http_address'])){
                return array('http_code'=> self::STATUS_BAD_REQUEST, 
                	'response'=> array(
	                    'status'   => "MissingFields",
	                    'message'  => "Missing field on position: ".$key,
	                    'required' => array('httpAddress')
	                    )
                );
                
            }
        }
    }

    /**
     * Show options in header
     */
    public function options(){
        self::response(self::STATUS_OK, array(), array('POST', 'OPTIONS'));
    }

    /**
     * @return \App\Service\RedirectUri
     */
    public function getRedirectUriService(){
        return $this->redirectUriService;
    }

    /**
     * @param \App\Service\AUthorize $redirectUriService
     */
    public function setRedirectUriService($redirectUriService){
        $this->redirectUriService = $redirectUriService;
    }

    /**
     * @return array
     */
    public function getOptions(){
        return $this->options;
    }
}