<?php
//Easy image check function
//Value 1: temporary file data
//Value 2: Mb allowed to upload (Default: 2mb)
//Extension allowed: PNG/GIF/JPG/JPEG
function is_image($f, $mb = 2) {
	if ($f['size'] < 1024*$mb*1024 && ($f['type'] == 'image/gif' || $f['type'] == 'image/jpg' || $f['type'] == 'image/jpeg' || $f['type'] == 'image/png'))
		return true;
	else
		return false;
}

//$data[] = array
//countPerPage - Count per page
//maxNumShow - Maximum number to show before ...
//pageNum - current page number
//tableName - for sql query
//where - for sql query WHERE column
//field to count - (WARNING: MUST NOT USE `*` IN THIS FUNCTION) default: id
//cms - if 1, adding html for cms pages
function pages($data) {
    $data['pageNum'] = abs((int)$data['pageNum']);
    $return = new stdClass();
    
    if (!isset($data['tableName']) || !$data['tableName']) {
		return 'Table name for pages not set';
    }
    
	if ($data['pageNum'] == 0) {
		$data['pageNum'] = 1;
    }
    if (!isset($data['maxNumShow']) || !$data['maxNumShow']) {
		$data['maxNumShow'] = 10;
    }
    if (!isset($data['pageNum']) && !$data['pageNum']) {
		$data['pageNum'] = 3;
    }
    if (!isset($data['where']) || !$data['where']) {
		$data['where'] = '';
    }
    if (!isset($data['count']) || !$data['count']) {
		$data['count'] = 'id';
    }
    if (!isset($data['cms']) || !$data['cms']) {
		$data['cms'] = 0;
    }
    if (!isset($data['additionalLink']) || !$data['additionalLink']) {
        $data['additionalLink'] = '';
    }
    
    if ($data['cms'] == 1) {
        $link = _cfg('cmssite');
    }
    else {
        $link = _cfg('href');
    }

	$strt = $data['pageNum'].'0'; //must fix this value if you want to make some different value then *0 for example 4 or 8
	$strt *= ($data['countPerPage']/10);
	$strt -= $data['countPerPage'];
    
    if (isset($_GET['val1']) && $_GET['val1']) {
        $link .= '/'.$_GET['val1'];
    }
    else if ($data['cms'] == 1) {
        $link .= '/'.$_POST['page'].$data['additionalLink'];
    }

    $link .= '/page/%page%';

    if ($_POST) {
    	foreach($_POST as $k => $v) {
    		if ((int)substr($k, -1) > 3) {
    			$link .= '/'.$v;
    		}
    	}
    }
    
    $return->countPerPage = $data['countPerPage'];

	$row = Db::fetchRow('SELECT COUNT(`'.$data['count'].'`) AS `count` FROM `'.$data['tableName'].'` '.$data['where'].'');
	$so = $row->count;
	
	if ($so > $data['countPerPage']) {
		$so = $so/$data['countPerPage'];
		if ($so > round($so)) $so = round($so)+1;
		if ($so < round($so)) $so = round($so);
		$lastp = $so;

		$html = '';
		
		$st = $data['maxNumShow']+2;
		$lt = $data['maxNumShow']+1;
		
		$html .= '<div class="pages">';
		for ($i=1;$i<=$lastp;++$i) {
			if ($data['pageNum'] != 1 && $i == 1) {
				$bl = $data['pageNum'] - 1;
				$html .= '<a href="'.str_replace('%page%', $bl, $link).'" class="inpage"><<</a>';
			}
			
			if ($data['pageNum'] == $i) {
				$html .= '<b class="inpage">'.$i.'</b>';
			}
			
			if ($data['pageNum'] > $st && $i == 1) {
				$html .= '<a href="'.str_replace('%page%', '1', $link).'" class="inpage">1</a> ... ';
			}
			
			for ($j=1;$j<=$data['maxNumShow'];++$j) {
				if ($data['pageNum']+$j==$i||$data['pageNum']-$j==$i) {
					if (($data['pageNum'] > $st && $i == 1) || ($data['pageNum'] < $lastp - $lt && $i == $lastp - 1)) {
						//Drat those 2 and last piece
					}
					else {
						$html .= '<a href="'.str_replace('%page%', $i, $link).'" class="inpage">'.$i.'</a>';
					}
					break;
				}
			}
            
			if ($data['pageNum'] <= ($lastp - $lt) && $i == $lastp) {
				$html .= ' ... <a href="'.$link.'/page/'.$lastp.'" class="inpage">'.$lastp.'</a>';
			}
			
			if (round($so) == $i && $data['pageNum'] != round($lastp)) {
				$fl = $data['pageNum'] + 1;
				$html .= '<a href="'.str_replace('%page%', $fl, $link).'" class="inpage">>></a>';
			}
		}
		$html .= '</div>';
        
        $return->start = $strt;
        $return->html = $html;
	}
	else {
        $return->start = 0;
		$return->html = '';
    }
    
    return $return;
}

//Count per page
//Return data count/Maximum number to show before ...
//Page number
//Table name
//If needed WHERE parameter
//Count parameter (WARNING: MUST NOT USE `*` IN THIS FUNCTION)
function ajaxPages($cpp = 10, $rdc = 3, $pn, $tn, $where = '', $count = 'id') {
    $db = new Db();
	$pn = abs((int)$pn);
	if ($pn == 0)
		$pn = 1;

	$strt = $pn.'0'; //must fix this value if you want to make some different value then *0 for example 4 or 8
	$strt *= ($cpp/10);
	$strt -= $cpp;

	$q = mysql_query('SELECT COUNT(`'.$count.'`) FROM `'.$tn.'` '.$where.'');
	$so = mysql_result($q, 0);
	
	if ($so > $cpp) {
		$so = $so/$cpp;
		if ($so > round($so)) $so = round($so)+1;
		if ($so < round($so)) $so = round($so);
		$lastp = $so;

		$pnd = '';
		
		$st = $rdc+2;
		$lt = $rdc+1;
		
		$pnd .= '<div class="pages">';
		for ($i=1;$i<=$lastp;++$i) {
			if ($pn != 1 && $i == 1) {
				$bl = $pn - 1;
				$pnd .= '<a href="'._SITE.'admin/#'.$_GET['p'].'/'.$bl.$plink.'" class="inpage"><<</a>';
			}
			
			if ($pn == $i) {
				$pnd .= '<b class="inpage">'.$i.'</b>';
			}
			
			if ($pn > $st && $i == 1) {
				$pnd .= '<a href="'._SITE.'admin/#'.$_GET['p'].'/1" class="inpage">1</a> ... ';
			}
			
			for ($j=1;$j<=$rdc;++$j) {
				if ($pn+$j==$i||$pn-$j==$i) {
					if (($pn > $st && $i == 1) || ($pn < $lastp - $lt && $i == $lastp - 1)) {
						//Drat those 2 and last piece
					}
					else {
						$pnd .= '<a href="'._SITE.'admin/#'.$_GET['p'].'/'.$i.$plink.'" class="inpage">'.$i.'</a>';
					}
					break;
				}
			}
			
			if ($pn < $lastp - $lt && $i == $lastp) {
				$pnd .= ' ... <a href="'._SITE.'admin/#'.$_GET['p'].'/'.$lastp.'" class="inpage">'.$lastp.'</a>';
			}
			
			if (round($so) == $i && $pn != round($lastp)) {
				$fl = $pn + 1;
				$pnd .= '<a href="'._SITE.'admin/#'.$_GET['p'].'/'.$fl.$plink.'" class="inpage">>></a>';
			}
		}
		$pnd .= '</div>';
		return $cpp.'!'.$strt.'!'.$pnd;
	}
	else
		return $cpp.'!0!'.$pnd;
}

function _cfg($key = '') {
    global $cfg;
    
    if (!$key) {
        return $cfg;
    }
    else if (!isset($cfg[$key])) {
    	return 'Config not found';
    }
    
    return $cfg[$key];
}

//Writting strings
function t($key) {
	global $str;
	
	$key = strtolower($key);
    
	if (isset($str[$key])) {
        if (_cfg('language') == 'lv') {
            return html_entity_decode($str[$key]);
        }
        return $str[$key];
    }
	
    return $key;
}

//Writing admin strings
function at($key = '') {
	global $astr;
    
    if (isset($astr[$key]) && $astr[$key]) {
	   return $astr[$key];
	}
    else {
        return $key;
    }
    
    return $key;
}

//Redirecting user
function go($link = '') {
    if (!$link) {
        $link = _cfg('site');
    }
    
    echo '<script>window.location.replace("'.$link.'");</script>';
    exit();
}

//Dumping array data, it is much more cooler then print_r
function dump($data, $font = 10) {
	echo '<div style="text-align: left; padding-left: '.$font.'px;"><pre>';
	print_r($data);
	echo '</pre></div>';
}

//Dumping array data, it is much more cooler then print_r
function ddump($data, $font = 10) {
	echo '<div style="text-align: left; padding-left: '.$font.'px;"><pre>';
	print_r($data);
	echo '</pre></div>';
	exit();
}

//Month value to show month as 01,02,11 and not like 1,2,11
function m_value($p) {
	if ($p > 9) {
		return $p;
	}
	else {
		return '0'.$p;
	}
}
?>