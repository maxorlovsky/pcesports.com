<?php

class Update extends System
{
    public function __construct() {
    	parent::__construct();
    }
    
    public function run($params = array()) {
        if (!$params) {
            return '0;Parameters not set';
        }
        
        if (!is_writable(_cfg('cmsdir').'/classes/system.php')) {
            return '0;Update impossible, editing files are forbidden. Try <a href="'._cfg('cmssite').'/#update">manual update</a>';
        }
        
        $answer = $this->runAPI($params);
        
        if ($answer && substr($answer,0,1) == '2') {
            $json = json_decode(substr($answer,2));
            
            if (isset($json->files) && $json->files != '-') {
                $data = $this->updateFiles($json->files);
                if ($data !== true) {
                    return $data;
                }
            }
            
            if (isset($json->deleteFiles) && $json->deleteFiles != '-') {
                $data = $this->deleteFiles($json->deleteFiles);
                if ($data !== true) {
                    return $data;
                }
            }
            
            if ($json->sql != '-') {
                $data = $this->updateSql($json->sql);
                if ($data !== true) {
                    return $data;
                }
            }
            
            if (isset($json->composer) && $json->composer != '-') {
                $data = $this->updateComposer($json->composer);
                if ($data !== true) {
                    return $data;
                }
            }
            
            if (isset($json->index) && $json->index != '-') {
                $data = $this->updateIndex($json->index);
                if ($data !== true) {
                    return $data;
                }
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
    
    protected function updateFiles($files) {
        $error = '0;';
        foreach($files as $k => $v) {
            //Updating files
            if (file_exists(_cfg('cmsdir').$k) && !is_writable(_cfg('cmsdir').$k)) {
                $error .= '<p>File .'._cfg('cmsdir').$k.' is not writeable</p>';
            }
        }
        
        if ($error == '0;') {
            foreach($files as $k => $v) {
                file_put_contents(_cfg('cmsdir').$k, base64_decode($v));
            }
            
            return true;
        }
        
        return $error;
    }
    
    protected function updateSql($sql) {
        $breakdown = explode(';', $sql);
        foreach($breakdown as $v) {
            if (trim($v)) {
                Db::query(trim($v));
                if (Db::error()) {
                    return '0;There was an error while trying to update SQL:<br>'.Db::error().'<br />Make <a href="'._cfg('cmssite').'/#update">manual update</a> <b>ONLY FOR SQL</b>';
                }
            }
        }
        
        return true;
    }
    
    protected function updateComposer($composer) {
        if (!is_writable($_SERVER['DOCUMENT_ROOT'].'/composer.json')) {
            return '0;Update composer.json is impossible, editing files are forbidden. Try <a href="'._cfg('cmssite').'/#update">manual update</a>';
        }
        
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/composer.json', base64_decode($composer));
        return true;
    }
    
    protected function deleteFiles($files) {
        $error = '0;';
        foreach($files as $k => $v) {
            //Updating files
            if (!is_writable(_cfg('cmsdir').$k)) {
                $error .= '<p>File .'._cfg('cmsdir').$k.' is not writeable</p>';
            }
        }
        
        if ($error == '0;') {
            foreach($files as $k => $v) {
                unlink(_cfg('cmsdir').$k);
            }
            
            return true;
        }
        
        return $error;
    }
    
    protected function updateIndex($index) {
        if (!is_writable($_SERVER['DOCUMENT_ROOT'].'/index.php')) {
            return '<p>File .'.$_SERVER['DOCUMENT_ROOT'].'/index.php is not writeable</p>';
        }
        
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/index.php', base64_decode($index));
        
        return true;
    }
}