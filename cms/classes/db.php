<?php

class Db
{
    private static $connection = NULL;
    
    public function __construct() {}
    
    public static function connect() {
        if (!self::$connection) {
            self::$connection = new mysqli(_cfg('dbHost'), _cfg('dbUser'), _cfg('dbPass'), _cfg('dbBase'), _cfg('dbPort'));
            if(self::$connection->connect_error) {
                system::errorMail(
        			'[FATAL] TheMages CMS:',
        			get_class(), __LINE__,
        			"Exc: ${msg}\n".
        			" on: "._cfg('site')."\n
                    Mysql response: ".mysqli_connect_error()." (".self::$connection->connect_errno.") - (".self::$connection->connect_error.")"
                );
                
                exit('SQL Error');
            }
            
            self::$connection->query('SET NAMES "utf8"');
        }
        
        return self::$connection;
    }
    
    public static function close() {
        $answer = self::$connection->close();
        self::$connection = NULL;
        return $answer;
    }
    
    public static function query($query) {
        $result = self::$connection->query($query);
        if (!$result) {
            return "Mysql response: ".mysqli_connect_error()." (".self::$connection->connect_errno.") - (".self::$connection->connect_error.")";
        }
        
        return $result;
    }
    
    public static function query_fatal($query) {
		if ( self::query($query) === false ){
			error_log( 'DB Error: '.$query );
            system::errorMail(
    			'[FATAL] TheMages CMS:',
    			get_class(), __LINE__,
    			"Exc: ${msg}\n".
    			" on: "._cfg('site')."\n
                Mysql response: ".self::$connection->error."\n
                Query: ".$query
            );
			die('DB Error');
		}
    }
    
    public static function fetchRow($query) {
        $result = self::query($query);
        if ($result->num_rows == 0) {
            return false;
        }
        
        $row = $result->fetch_object();
        
        return $row;
    }
    
    public static function fetchRows($query) {
        $array = array();
        $result = self::query($query);
        if ($result->num_rows == 0) {
            return false;
        }
        
        while($fetchedResults = $result->fetch_object()) {
            $array[] = $fetchedResults;
        }
        $rows = (object)$array;
        
        return $rows;
    }
    
    public static function lastId() {
        return self::$connection->insert_id;
    }
    
    public static function escape($variable) {
        if (!is_array($variable)) {
            $string = trim($variable);
            
            return self::$connection->real_escape_string($string);
        }
        else {
            $array = array();
            foreach($variable as $f) {
                $f = trim($f);
                
                $array[] = self::$connection->real_escape_string($f);
            }
            
            return $array;
        }
    }
}