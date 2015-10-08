<?php

class Db
{
    private static $connection = NULL;
    
    public static function connect() {
        if (!self::$connection) {
            self::$connection = new mysqli(_cfg('dbHost'), _cfg('dbUser'), _cfg('dbPass'), _cfg('dbBase'), _cfg('dbPort'));
            if(self::$connection->connect_error) {
                System::errorMail(
        			'[FATAL] '.$_SERVER['SERVER_NAME'].':',
        			get_class(),
                    __LINE__,
        			"Exc: ${msg}\n on: "._cfg('site')."\n Mysql response: ".mysqli_connect_error()." (".self::$connection->connect_errno.") - (".self::$connection->connect_error.")"
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
        if (!$result && _cfg('env') != 'prod') {
            echo self::error();
            return false;
        }
        else if (!$result) {
            return "Mysql response: ".mysqli_connect_error()." (".self::$connection->connect_errno.") - (".self::$connection->connect_error.")";
        }
        
        return $result;
    }
    
    public static function query_fatal($query) {
		if ( self::query($query) === false ){
			error_log( 'DB Error: '.$query );
            system::errorMail(
    			'[FATAL] '.$_SERVER['SERVER_NAME'].':',
    			get_class(),
                __LINE__,
    			"Exc: ${msg}\n on: "._cfg('site')."\n Mysql response: ".self::$connection->error."\n Query: ".$query
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
    
    public static function error() {
    	if(self::$connection->errno) {
    		return self::$connection->errno.': '.self::$connection->error;
        }
        
    	return false;
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
    
    public static function escape_tags($variable, $allowable_tags = '') {
        if (!is_array($variable)) {
            $string = trim(strip_tags($variable, $allowable_tags));
            
            return self::$connection->real_escape_string($string);
        }
        else {
            $array = array();
            foreach($variable as $f) {
                $f = trim(strip_tags($f, $allowable_tags));
                
                $array[] = self::$connection->real_escape_string($f);
            }
            
            return $array;
        }
    }
    
    public static function multi_query($query) {
        if (self::$connection == NULL) {
            return false;
        }
        
        return self::$connection->multi_query($query);
    }
    
    public static function store_result() {
        return self::$connection->store_result();
    }
    
    public static function more_results() {
        return self::$connection->more_results();
    }
    
    public static function next_result() {
        return self::$connection->next_result();
    }
}