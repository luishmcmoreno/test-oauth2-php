<?php

namespace Library;

use Library;

class MyLogger extends Library
{

	private $patch;
	private $log_file  = "MyLoggerErrors.log";
    
    /**
     * Construct
     */
    public function __construct(){
        $this->patch = __DIR__.'/../../../logs/';
    }
    
    public function register($data, $file = false){

        if(empty($data['last_call'])){
            $backtrace = debug_backtrace();
            $data['last_call'] = $backtrace[0];
        }

        if($file){
            $file = fopen($this->patch.$file, 'a');
        } else{
            $file = fopen($this->patch.$this->log_file, 'a');
        }

        fwrite($file, "\n\n");

    	if(is_array($data)){
    		foreach ($data as $key => $value) {
    			fwrite($file, "[".$key."] = ".print_r($value,true)."\n");
    		}
    	} else{
    		fwrite($file, $data."\n");
    	}


        fwrite($file, "[REGISTER DATE] = ".date('d/m/Y - H:i:s')."\n");
    	fwrite($file, "\n\n");
    	fclose($file);
    }
}