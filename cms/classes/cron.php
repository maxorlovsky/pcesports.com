<?php

class Cron extends System
{
    
    public function __construct() {
        parent::__construct();
    }
    
    private function atomicFileReplace( $file, $data )
    {
		$file_tmp = $file.'.tmp';
		
		return (file_put_contents($file_tmp, $data) !== FALSE) && rename($file_tmp, $file);
    }
}