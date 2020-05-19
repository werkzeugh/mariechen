<?php

use SilverStripe\Core\Environment;

class DBMS
{
    
    var $mdb_arr;
    var $confs=[];
    
    public function local_get_mdb($type = "silverstripe")
    {
        
        //phpinfo();
        
        if (!$this->mdb_arr[$type]) {
            $this->connect_mdb($type);
        }
        
        return $this->mdb_arr[$type];
    }
    
    public static function getMdb($type = 'silverstripe')
    {
        
        return singleton('DBMS')->local_get_mdb($type);
    }
    
    public function getConfForType($type)
    {
        if (array_key_exists($type, $this->confs)) {
            return $this->confs[$type];
        }
    }
    
    public static function setConfForType($type, $host, $username, $password, $database)
    {
        singleton('DBMS')->confs[$type]=[$host, $username, $password, $database];
    }

    public function connect_mdb($type = 'silverstripe')
    {
        global $databaseConfig;
        
        if ($type=='silverstripe') {
            $host=Environment::getEnv('SS_DATABASE_SERVER');
            $username=Environment::getEnv('SS_DATABASE_USERNAME');
            $password=Environment::getEnv('SS_DATABASE_PASSWORD');
            $database=Environment::getEnv('SS_DATABASE_NAME');
        } else {
            $conf=$this->getConfForType($type);
            if (!$conf) {
                die('Error, no connection to the database for type'.$type);
            }
            [$host, $username, $password, $database]=$conf;
        }
        
        
        $this->mdb_arr[$type] = @new MDBDid($host, $username, $password, $database);
        if ($this->mdb_arr[$type]->connect_error) {
            die('Error, no connection to the database.');
        }
        $this->mdb_arr[$type]->query("SET NAMES 'utf8'");
        
        return 1;
    }

    public static function isError($var)
    {
        if (is_array($var) && array_key_exists("error", $var)) {
            return true;
        }
        return false;
    }
}
