<?php
class Ajax extends System
{
    public function __construct() {
        parent::__construct();
    }
    
    private $allowed_ajax_methods = array(
		'newsVote',
	);
	
    public function ajaxRun($data) {
    	$controller = $data['ajax'];
        
        if ( in_array( $controller, $this->allowed_ajax_methods ) ) {
            echo $this->$controller($data);
            return true;
        }
        else {
            echo '0;'.at('controller_not_exists');
            return false;
        }
    }
    
    protected function newsVote($data) {
    	$row = Db::fetchRow('SELECT * FROM `news_likes`'.
    		'WHERE `news_id` = '.(int)$data['id'].' AND `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
    		'LIMIT 1'
   		);
    	
    	if ($row) {
    		$num = '- 1';
    		Db::query('DELETE FROM `news_likes`'.
    			'WHERE `news_id` = '.(int)$data['id'].' AND `ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
    			'LIMIT 1'
    		);
    	}
    	else {
    		$num = '+ 1';
    		Db::query('INSERT INTO `news_likes` SET '.
    			'`news_id` = '.(int)$data['id'].', '.
    			'`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'
    		);
    	}
    	
    	Db::query('UPDATE `news`'.
    		'SET `likes` = `likes` '.$num.' '.
    		'WHERE `id` = '.(int)$data['id'].' '.
    		'LIMIT 1'
    	);
    	
    	return '1;'.$num;
    }
}
