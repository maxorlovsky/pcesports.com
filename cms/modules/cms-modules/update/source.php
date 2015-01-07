<?php
class Update
{
    public $version;
    
    function __construct() {
        $this->version = $this->fetchVersion();
        
        return $this;
    }
    
    protected function fetchVersion() {
    	$ccv = file('http://cms.themages.net/admin/updates/CHANGELOG.txt');
        
        if ($ccv[0]) {
            $version = trim($ccv[0]);
        }
        else {
            $version = 0;
        }
    	
    	return $version;
    }
}