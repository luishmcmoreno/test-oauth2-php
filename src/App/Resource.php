<?php

namespace CampuseroOAuth2\App;

use CampuseroOAuth2\AppContainer as AppContainer;
use App\Library\MyLogger as MyLogger;
abstract class Resource
{
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NO_CONTENT = 204;

    const STATUS_MULTIPLE_CHOICES = 300;
    const STATUS_MOVED_PERMANENTLY = 301;
    const STATUS_FOUND = 302;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_USE_PROXY = 305;
    const STATUS_TEMPORARY_REDIRECT = 307;

    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_METHOD_NOT_ALLOWED = 405;
    const STATUS_NOT_ACCEPTED = 406;
    const STATUS_CONFLICT = 409;

    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED = 501;

    

    /**
     * @var App\AppContainer
     */
    private $slim;

    /**
     * Construct
     */
    public function __construct(){

        $this->setSlim(AppContainer::getInstance());
        $this->init();
    }

    /**
     * Default init, use for overwrite only
     */
    public function init(){
    }

    /**
     * Converte indices do array de under_score  para camelCase indices
     * @param   array   $array          array que será convertido
     * @param   array   $arrayHolder    array pai
     * @return  array   camelCase array
     */
    public static function camelCaseKeys($array, $arrayHolder = array()) {
        $camelCaseArray = !empty($arrayHolder) ? $arrayHolder : array();
        foreach ($array as $key => $val) {
            $newKey = @explode('_', $key);
            array_walk($newKey, create_function('&$v', '$v = ucwords($v);'));
            $newKey = @implode('', $newKey);
            $newKey{0} = strtolower($newKey{0});

            if (!is_array($val)) {
                $camelCaseArray[$newKey] = $val;
            } else {
                $camelCaseArray[$newKey] = array();
                $camelCaseArray[$newKey] = Resource::camelCaseKeys($val, $camelCaseArray[$newKey]);
            }
        }
        return $camelCaseArray;
    }

    /**
     * Converte indices do array de camelCase para under_score+lowercase type
     * @param   array   $array          array que será convertido
     * @param   array   $arrayHolder    array pai
     * @return  array   under_score array
     */
    public static function underscoreKeys($array, $arrayHolder = array()) {
        $underscoreArray = !empty($arrayHolder) ? $arrayHolder : array();
        if(is_array($array)){
            foreach ($array as $key => $val) {
                $newKey = preg_replace('/[A-Z]/', '_$0', $key);
                $newKey = strtolower($newKey);
                $newKey = ltrim($newKey, '_');
                if (!is_array($val)) {
                    $underscoreArray[$newKey] = $val;
                } else {
                    $underscoreArray[$newKey] = array();
                    $underscoreArray[$newKey] = Resource::underscoreKeys($val, $underscoreArray[$newKey]);
                }
            }
        }
        return $underscoreArray;
    }

   
    /**
     * @param $resource
     * @return mixed
     */
    public static function load($resource){
        $class = __NAMESPACE__ . '\\Resource\\' . ucfirst($resource);
        if (!class_exists($class)) {
            return null;
        }

        return new $class();
    }

    /**
     * @return \Slim\App
     */
    public function getSlim(){

        return $this->slim;
    }

    /**
     * @param \Slim\App $slim
     */
    public function setSlim($slim){

        $this->slim = $slim;
    }
}