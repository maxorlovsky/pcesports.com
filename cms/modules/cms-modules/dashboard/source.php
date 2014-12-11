<?php
class Dashboard
{
    function __construct() {
        $this->line = $this->fetchChangeLog();
        
        return $this;
    }
    
    protected function fetchChangeLog() {
    	$ccv = file('http://cms.themages.net/admin/updates/CHANGELOG.txt');
        
        if ($ccv[0]) {
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
        }
        else {
            $answer['version'] = '<i>Not available</i>';
            $answer['changeLog'] = '<i>Change log not accessible at the moment</i><br />';
        }
    	
    	return $answer;
    }
}