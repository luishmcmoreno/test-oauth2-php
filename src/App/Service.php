<?php

namespace CampuseroOAuth2\App;
use CampuseroOAuth2\App\Library\MyLogger as MyLogger;
use \PDO;

abstract class Service
{
    protected $database;
    public    $log_file = "service.log";

    public function getConnection(){

        $ini = parse_ini_file(__DIR__ . '/../../config/local.ini');

        $host = $ini['host'];
        $name = $ini['name'];
        $user = $ini['user'];
        $pass = $ini['pass'];

        $myPDO = new PDO(
            "pgsql:host=$host;dbname=$name",
            "$user",
            "$pass"
        );

        $this->database = $ini['name'];

        return $myPDO;
    }

    public function getDataConnection(){

	    $ini = parse_ini_file(__DIR__ . '/../../config/local.ini');

	    $host = $ini['host'];
	    $name = $ini['name'];
	    $user = $ini['user'];
	    $pass = $ini['pass'];

	    $conection = ("host=$host dbname=$name user=$user password=$pass");
	    return $conection;
	}

	/**
	 * @param array with lat and lng
	 */
	public function getPolygonString($polygon_array){

		$polygon_string = "POLYGON((";

		foreach ($polygon_array as $key => $value) {
			$polygon_string .= $value['lng']." ".$value['lat'].",";
		}
		$polygon_string = rtrim($polygon_string, ",");
		$polygon_string .= "))";

		return $polygon_string;
	}

	/**
	 * @param statment ou conn
	 */
	public function verifyBadExecute($PDO){

        $myLogger  = new MyLogger();
        $backtrace = debug_backtrace();

        if($PDO->errorInfo()[2]){

            $log = array(
                 'Error code'                    => $PDO->errorInfo()[0]
                ,'Driver specific error code'    => $PDO->errorInfo()[1]
                ,'Driver specific error message' => $PDO->errorInfo()[2]
                ,'File'      => $this->log_file
                ,'last_call' => $backtrace[0]
                ,'prev_call' => $backtrace[1]
            );



            $myLogger->register($log, $this->log_file);
			return false;
        } else{
        	return true;
        }
	}

    /*
     * Monta a condição dinamica para busca de um único campo dado um parametro
     */
    public function makeConditionSplitText($field, $alias, $table, $field_value){
        
        $num_blank   = $this->counMaxFieldBlankSpace($field, $table) + 1;
        $field_value = explode(' ', $field_value);

        $condition = "";

        $size = count($field_value) - 1;
        foreach($field_value as $key => $value){
            //Abre
            $condition .= " (";

            //Conteúdo
            for($i = 1; $i <= $num_blank; $i++){
                $condition .= " split_part(".$alias.".".$field.", ' ', ".$i.") ilike '".$value."%'  ";
                $condition .= $i == $num_blank ? '' : ' or ';
            }

            //Fecha
            $condition .= " )";

            //Verifica continuidade
            $condition .= $key == $size ? '' : ' AND ';
            $size-1;
        }

        return $condition;
    }

    /*
     * Recupera o nome com maior espaços em branco
     */
    public function counMaxFieldBlankSpace($field, $table){
        
        $conn = $this->getConnection();

        $query = $conn->prepare("
            SELECT
                max(length(".$field.") - length(regexp_replace(".$field.",' ','','g'))) as maximo
            FROM
                ".$table." tb1
        ");
        $query->execute();
        $line = $query->fetch(PDO::FETCH_ASSOC);
        $conn = null;

        if(!empty($line)){
            return $line['maximo'];
        } else {
            return 0;
        }
    }

    /*
     * Recupera o dia corrente de acordo com a data atual. Modelo ISO-8601.
     */
    public function getCurrentDay(){

        $name_day = array(
            1 => 'sunday',
            2 => 'monday',
            3 => 'tuesday',
            4 => 'wednesday',
            5 => 'thursday',
            6 => 'friday',
            7 => 'saturday'
        );

        return $name_day[date('N')];
    }

    /*
     * Gera um conjunto de caracteres randômicos
     */
    public function generateRandomPassword(){

        $range_start = 97;
        $range_end   = 122;
        $random_string = rand(5, 100);
        $random_string_length = 8;

        for ($i = 0; $i < $random_string_length; $i++) {
          $ascii_no = round(mt_rand($range_start , $range_end));
          $random_string .= chr($ascii_no);
        }
        $new_password  = substr($random_string, 0, 8);

        return $new_password;
    }

    /*
     * Gera um conjunto de caracteres randômicos
     */
    public function generateRandomCharacter($size){

        $range_start = 97;
        $range_end   = 122;
        $random_string = rand(5, 100);
        $random_string_length = $size;

        for ($i = 0; $i < $random_string_length; $i++) {
          $ascii_no = round(mt_rand($range_start , $range_end));
          $random_string .= chr($ascii_no);
        }
        $new_password  = substr($random_string, 0, 8);

        return $new_password;
    }

    /**
     * Generate URL ID
     * @return array
     */
    public function generateUrlId($dba_name) {
        $dba_name = $this->removeAcentos($dba_name,'');
        $dba_name = strtolower(str_replace(' ', '', $dba_name));
        return preg_replace('/[^A-Za-z0-9\-]/', '', $dba_name);
    }

    /***
     * Função para remover acentos de uma string
     */
    public function removeAcentos($string, $slug = false) {
        $string = utf8_decode($string);
        
        // Código ASCII das vogais
        $ascii['a'] = range(224, 230);
        $ascii['e'] = range(232, 235);
        $ascii['i'] = range(236, 239);
        $ascii['o'] = array_merge(range(242, 246), array(240, 248));
        $ascii['u'] = range(249, 252);
        // Código ASCII dos outros caracteres
        $ascii['b'] = array(223);
        $ascii['c'] = array(231);
        $ascii['d'] = array(208);
        $ascii['n'] = array(241);
        $ascii['y'] = array(253, 255);
        foreach ($ascii as $key=>$item) {
            $acentos = '';
            foreach ($item AS $codigo) $acentos .= chr($codigo);
            $troca[$key] = '/['.$acentos.']/i';
        }
        $string = preg_replace(array_values($troca), array_keys($troca), $string);
        // Slug?
        if ($slug) {
            // Troca tudo que não for letra ou número por um caractere ($slug)
            $string = preg_replace('/[^a-z0-9]/i', $slug, $string);
            // Tira os caracteres ($slug) repetidos
            $string = preg_replace('/' . $slug . '{2,}/i', $slug, $string);
            $string = trim($string, $slug);
        }
        return $string;
    }

    /***
     * Função para formatar um valor em xxx.xx
     */
    public function formatNumeric($number) {
        
        $number = str_replace(',','.', $number);
        $number = number_format($number,2,'.','');

        return $number;
    }
}