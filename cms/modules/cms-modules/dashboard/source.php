<?php
class Dashboard
{
    
    function __construct() {
        $this->line = $this->fetchChangeLog();
        
        return $this;
    }
    
    protected function fetchChangeLog() {
    	$ccv = file('http://cms.themages.net/admin/updates/CHANGELOG.txt');
    	$answer['version'] = trim($ccv[0]);
    	
    	$changeLog = '';
    	$i = 3;
    	foreach($ccv as $f) {
    		$changeLog .= $f.'<br />';
    		if (!trim($f)) {
    			--$i;
    		}
    		if ($i == 0) {
    			break(1);
    		}
    	}
    	unset($ccv, $f);
    	
    	$answer['changeLog'] = $changeLog;
    	
    	return $answer;
    }
}