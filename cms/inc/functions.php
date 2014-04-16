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

//Count per page
//Return data count/Maximum number to show before ...
//Page number
//Table name
//If needed WHERE parameter
//Count parameter (WARNING: MUST NOT USE `*` IN THIS FUNCTION)
function pages($cpp = 10, $rdc = 3, $pn, $tn, $where = '', $count = 'id') {
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
				$pnd .= '<a href="'._A.'#'.$_GET['p'].'/page/'.$bl.$plink.'" class="inpage"><<</a>';
			}
			
			if ($pn == $i) {
				$pnd .= '<b class="inpage">'.$i.'</b>';
			}
			
			if ($pn > $st && $i == 1) {
				$pnd .= '<a href="'._A.'#'.$_GET['p'].'/page/1" class="inpage">1</a> ... ';
			}
			
			for ($j=1;$j<=$rdc;++$j) {
				if ($pn+$j==$i||$pn-$j==$i) {
					if (($pn > $st && $i == 1) || ($pn < $lastp - $lt && $i == $lastp - 1)) {
						//Drat those 2 and last piece
					}
					else {
						$pnd .= '<a href="'._A.'#'.$_GET['p'].'/page/'.$i.$plink.'" class="inpage">'.$i.'</a>';
					}
					break;
				}
			}
			
			if ($pn < $lastp - $lt && $i == $lastp) {
				$pnd .= ' ... <a href="'._A.'#'.$_GET['p'].'/page/'.$lastp.'" class="inpage">'.$lastp.'</a>';
			}
			
			if (round($so) == $i && $pn != round($lastp)) {
				$fl = $pn + 1;
				$pnd .= '<a href="'._A.'#'.$_GET['p'].'/page/'.$fl.$plink.'" class="inpage">>></a>';
			}
		}
		$pnd .= '</div>';
		return $cpp.'!'.$strt.'!'.$pnd;
	}
	else
		return $cpp.'!0!'.$pnd;
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
    
	if (isset($str[$key])) {
	   return $str[$key];
    }
	
    return $key;
}

//Writing admin strings
function at($key = '') {
	global $astr;
    
    if (!$key) {
        return $astr;
    }
	else if ($astr[$key]) {
	   return $astr[$key];
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