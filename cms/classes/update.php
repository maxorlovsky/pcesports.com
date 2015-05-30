<?php

class Update extends System
{
    public function __construct() {
    	parent::__construct();
    }
    
    public function run($params = array()) {
        $checked = array(
            'files'     => 0,
            'delete'    => 0,
            'sql'       => 0,
            'htaccess'  => 0,
            'index'     => 0,
        );

        if (!$params) {
            return '0;Parameters not set';
        }
        
        if (!is_writable(_cfg('cmsdir').'/classes/system.php')) {
            return '0;Update impossible, editing files are forbidden. Try <a href="'._cfg('cmssite').'/#update">manual update</a>';
        }
        
        //Fetching updated files
        $answer = $this->runAPI($params);
        
        if ($answer && substr($answer,0,1) == '2') {
            $json = json_decode(substr($answer,2));
            
            //Checking for files that system must update
            if (isset($json->files) && $json->files != '-') {
                $data = $this->checkFiles($json->files);
                if ($data !== true) {
                    return '0;'.$data;
                }

                $checked['files'] = 1;
            }
            
            //Checking for files that system must delete
            if (isset($json->deleteFiles) && $json->deleteFiles != '-') {
                $data = $this->checkFiles($json->deleteFiles);
                if ($data !== true) {
                    return '0;'.$data;
                }

                $checked['delete'] = 1;
            }
            
            if (isset($json->htaccess) && $json->htaccess != '-') {
                if (!is_writable($_SERVER['DOCUMENT_ROOT'].'/.htaccess')) {
                    return '0;Update .htaccess is impossible, editing files are forbidden. Try <a href="'._cfg('cmssite').'/#update">manual update</a>';
                }

                $checked['htaccess'] = 1;
            }
            
            if (isset($json->index) && $json->index != '-') {
                if (!is_writable($_SERVER['DOCUMENT_ROOT'].'/index.php')) {
                    return '<p>File .'.$_SERVER['DOCUMENT_ROOT'].'/index.php is not writeable</p>';
                }

                $checked['index'] = 1;
            }

            //Checking and updating SQL files
            if ($json->sql != '-') {
                $data = $this->updateSql($json->sql);
                if ($data !== true) {
                    return $data;
                }
            }

            //Updating files
            if ($checked['files'] == 1) {
                $this->updateFiles($json->files);
            }
            //Deleting files
            if ($checked['delete'] == 1) {
                $this->deleteFiles($json->deleteFiles);
            }
            //.htaccess update
            if ($checked['htaccess'] == 1) {
                $this->updateHtaccess($json->htaccess);
            }
            //index.php update
            if ($checked['index'] == 1) {
                $this->updateIndex($json->index);
            }
            
            $message = 'Success';
            if ($json->description != '-') {
                $message .= ' ('.$json->description.')';
            }
            
            return '1;'.$message;
        }
        else {
            return $answer;
        }
        
        return '0;System error';
    }

    protected function updateSql($sql) {
        Db::multi_query(trim($sql));
        if (Db::error()) {
            return '0;There was an error while trying to update SQL:<br>'.Db::error().'<br />Make <a href="'._cfg('cmssite').'/#update">manual update</a> <b>ONLY FOR SQL</b>';
        }

        return true;
    }

    protected function checkFiles($files) {
        $error = '0;';
        foreach($files as $k => $v) {
            //Updating files
            if (file_exists(_cfg('cmsdir').$k) && !is_writable(_cfg('cmsdir').$k)) {
                $error .= '<p>File .'._cfg('cmsdir').$k.' is not writeable</p>';
            }
        }
        
        if ($error == '0;') {
            return true;
        }
        
        return $error;
    }
    
    protected function updateFiles($files) {
        foreach($files as $k => $v) {
            file_put_contents(_cfg('cmsdir').$k, base64_decode($v));
        }
        
        return true;
    }

    protected function deleteFiles($files) {
        foreach($files as $f) {
            @unlink(_cfg('cmsdir').$f);
        }
        
        return true;
    }
    
    protected function updateHtaccess($htaccess) {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/.htaccess', base64_decode($htaccess));
        return true;
    }
    
    protected function updateIndex($index) {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/index.php', base64_decode($index));
        return true;
    }
}