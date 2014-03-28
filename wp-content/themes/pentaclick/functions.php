<?php
/**
 * @package Pentaclick
 * @since v1
 */

/**
 * @since v1
 */
function pentaclick_setup() {
	/*
	 * Makes Twenty Twelve available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Twenty Twelve, use a find and replace
	 * to change 'pentaclick' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'pentaclick', get_template_directory() . '/languages' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'pentaclick' ) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
}
add_action( 'after_setup_theme', 'pentaclick_setup' );

/**
 * Return the Google font stylesheet URL if available.
 * 
 * @since v1
 *
 * @return string Font stylesheet
 */
function pentaclick_get_font_url() {
	$font_url = '';
    
	$subsets = 'latin,cyrillic-ext,latin-ext,cyrillic';

	$protocol = is_ssl() ? 'https' : 'http';
	$query_args = array(
		'family' => 'PT+Sans:400,700',
		'subset' => $subsets,
	);
	$font_url = add_query_arg( $query_args, $protocol.'://fonts.googleapis.com/css' );

	return $font_url;
}

/**
 * Enqueue scripts and styles for front-end.
 */
function pentaclick_scripts_styles() {
	global $wp_styles;
    
    //Loading JS top
    wp_enqueue_script( 'pc-jquery', get_template_directory_uri() . '/js/jquery.min.js', array(), '1');
    wp_enqueue_script( 'pre-js', get_template_directory_uri() . '/js/pre-js.js', array(), '2');
    
    //Loading JS bottom
    wp_enqueue_script( 'isotope', get_template_directory_uri() . '/js/jquery.isotope.min.js', array(), '1', true);
    wp_enqueue_script( 'challonge', get_template_directory_uri() . '/js/jquery.challonge.js', array(), '1', true);
    wp_enqueue_script( 'post-js', get_template_directory_uri() . '/js/post-js.js', array(), '2', true);

    //Loading Google fonts
	$font_url = pentaclick_get_font_url();
	if ( ! empty( $font_url ) )
		wp_enqueue_style( 'pentaclick-fonts', esc_url_raw( $font_url ), array(), null );

	//Loading CSS
    wp_enqueue_style( 'style', get_stylesheet_uri(), 'array', 3 );
    wp_enqueue_style( 'isotope', get_template_directory_uri() . '/css/isotope.css', 'array', 1 );
    wp_enqueue_style( 'fonts', get_template_directory_uri() . '/css/fonts.css', 'array', 1 );
	
}
add_action( 'wp_enqueue_scripts', 'pentaclick_scripts_styles' );

/**
 * @since v1
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function pentaclick_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'pentaclick' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'pentaclick_wp_title', 10, 2 );

if ( ! function_exists( 'pentaclick_entry_meta' ) ) :
/**
 * @since v1
 *
 * @return void
 */
function pentaclick_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'pentaclick' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'pentaclick' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'pentaclick' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'pentaclick' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'pentaclick' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'pentaclick' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Register postMessage support.
 *
 * Add postMessage support for site title and description for the Customizer.
 *
 * @since Twenty Twelve 1.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 * @return void
 */
function pentaclick_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
add_action( 'customize_register', 'pentaclick_customize_register' );

/**
 * Admin functions 
*/
function pentaclick_admin_menus() {
     add_menu_page('PentaClick settings', 'PC eSports', 'edit_posts', 'options-pentaclick.php', '', '', 50);
     add_submenu_page('options-pentaclick.php', 'Tournament chats', 'Tournament chats', 'manage_options', 'pentaclick-chats.php');
     add_submenu_page('options-pentaclick.php', 'Emails sender', 'Emails sender', 'manage_options', 'pentaclick-emails.php');   
}
add_action("admin_menu", "pentaclick_admin_menus");

//Fixing qTranslate TinyMCE height
function content_textarea_height() {
    echo'
    <style type="text/css">
        #qtrans_textarea_content{ height:600px; }
    </style>
    ';
}
add_action('admin_head', 'content_textarea_height');

function getRewriteRules() {
    global $wp_rewrite; // Global WP_Rewrite class object
    return $wp_rewrite->rewrite_rules(); 
}
function addPentaClickRewrite() {
    global $wp_rewrite; // Global WP_Rewrite class object
    
    add_rewrite_tag('%team_id%', '([^/]*)');
    add_rewrite_tag('%code%', '([^/]*)');
    add_rewrite_rule('^verify/([^/]*)/([^/]*)/?','index.php?pagename=verify&team_id=$matches[1]&code=$matches[2]','top');
    add_rewrite_rule('^delete/([^/]*)/([^/]*)/?','index.php?pagename=delete&team_id=$matches[1]&code=$matches[2]','top');
    add_rewrite_rule('^profile/([^/]*)/([^/]*)/?','index.php?pagename=profile&team_id=$matches[1]&code=$matches[2]','top');
    
    $wp_rewrite->flush_rules(1);
}
addPentaClickRewrite();
//dump(getRewriteRules());

/**
 * Getting options for PentaClick
 *
 * @since v1
 */
$q = mysql_query('SELECT * FROM options');
$siteData = array();
while ($r = mysql_fetch_object($q)) {
    $siteData[$r->name] = $r->value;
}

$availableGames = array('lol', 'hs'); 
$breakdown = explode('.', $_SERVER['HTTP_HOST']);
$siteData['game'] = $breakdown[0];
if (!in_array($siteData['game'], $availableGames)) {
    $siteData['game'] = '';
}

function cOptions($key) {
    global $siteData;
    
    return $siteData[$key];
}

function runAPI($apiAdditionalData, $fullReturn = false) {
    $startTime = microtime(true);
    
    $apiUrl = 'http://prod.api.pvp.net/api/lol';
    $apiUrl .= $apiAdditionalData;
    $apiUrl .= '?api_key=d8339ebc-91ea-49d3-809d-abcb42df872a';
    
    
    mysql_query(
		'INSERT INTO `riot_requests` SET '.
		' `timestamp` = NOW(), '.
		' `ip` = "'.mysql_real_escape_string($_SERVER['REMOTE_ADDR']).'", '.
		' `data` = "'.$apiUrl.'"'
    );
    
    $lastId = sql_last_id();
    
	$ch = curl_init();
    
    //---
    curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
    curl_setopt($ch, CURLOPT_FAILONERROR, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 119s
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 0); // set POST method
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $apiArray); // add POST fields
    
    $response = curl_exec($ch); // run the whole process 
    
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if ($http_status == 400) {
		//$error = curl_error($ch);
        $error = 'Bad request';
	}
    else if ($http_status == 503) {
        $error = 'Service unavailable';
    }
    else if ($http_status == 500) {
        $error = 'Internal server error';
    }
    else if ($http_status == 401) {
        $error = 'Unauthorized';
    }
    else if ($http_status == 404) {
        $error = 'Not found';
    }
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime; //calculates total time taken
    
    mysql_query(
		'UPDATE `riot_requests` SET '.
			' `response` = "'.($error?$error:mysql_real_escape_string( $response )).'", '.
            ' `time` = "'.(float)$duration.'" '.
		' WHERE id='.$lastId
	);
	
	if ( $error )
	{
		return false;
	}
    
    if ($fullReturn === false) {
        $response = (array)json_decode($response);
        $response = array_values($response);
        $response = $response[0];
    }
    else {
        $response = json_decode($response);
    }
    
    return (object)$response;
}

function runChallongeAPI($apiAdditionalData, $apiArray = array(), $apiGetUrl = '') {
    $startTime = microtime(true);
    
    $apiUrl = 'https://api.challonge.com/v1/';
    $apiUrl .= $apiAdditionalData;
    $apiUrl .= '?api_key=5Md6xHmc7hXIEpn87nf6z13pIik1FRJY7DpOSoYa';
    if ($apiGetUrl) {
        $apiUrl .= '&'.$apiGetUrl;
    }
    
    $apiUrlLog = $apiUrl;
    if ($apiArray) {
        foreach($apiArray as $k => $v) {
            $apiUrlLog .= '&'.$k.'='.$v;
        }
    }

    mysql_query(
		'INSERT INTO `challonge_requests` SET '.
		' `timestamp` = NOW(), '.
		' `ip` = "'.mysql_real_escape_string($_SERVER['REMOTE_ADDR']).'", '.
		' `data` = "'.$apiUrlLog.'"'
    );
    
    $lastId = sql_last_id();
    
	$ch = curl_init();
    
    //---
    curl_setopt($ch, CURLOPT_URL, $apiUrl); // set url to post to
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 119s
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    if ($apiArray) {
        curl_setopt($ch, CURLOPT_POST, 1); //POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $apiArray); // add POST fields
    }
    else {
        curl_setopt($ch, CURLOPT_POST, 0); //GET
    }
    
    $response = curl_exec($ch); // run the whole process 
    //dump(curl_error($ch));
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if ($http_status == 401) {
        $error = 'Invalid API key';
	}
    else if ($http_status == 404 ) {
        $error = 'Object not found within your account scope';
    }
    else if ($http_status == 422) {
        $error = 'Validation error(s) for create or update method';
    }
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime; //calculates total time taken
    
    if ($apiArray) {
        $response = 'POST';
    }
    
    mysql_query(
		'UPDATE `challonge_requests` SET '.
			' `response` = "'.($error?$error:mysql_real_escape_string( $response )).'", '.
            ' `time` = "'.(float)$duration.'" '.
		' WHERE id='.$lastId
	);
	
	if ( $error )
	{
		return false;
	}
    
    if ($response == 'POST') {
        return true;
    }
    
    $response = json_decode($response); //
    
    return $response;
}

//Getting last insert ID from mysql query
function sql_last_id() {
	$q = mysql_query('SELECT LAST_INSERT_ID()');
	$id = mysql_result($q, 0, 0);
	return $id;
}

function sendMail($email, $subject, $msg) {
    // SMTP config
    $cfg['smtpMailName'] = 'pentaclickesports@gmail.com';
    $cfg['smtpVisionaryMail'] = 'info@pcesports.com';
    $cfg['smtpMailPort'] = '465';
    $cfg['smtpMailHost'] = 'ssl://smtp.gmail.com';
    $cfg['smtpMailPass'] = 'knyaveclickius888';
    $cfg['smtpMailFrom'] = 'PentaClick eSports';

    $mailData = 'Date: '.date('D, d M Y H:i:s')." UT\r\n";
    $mailData .= 'Subject: =?UTF-8?B?'.base64_encode($subject). "=?=\r\n";
    $mailData .= 'Reply-To: '.$cfg['smtpVisionaryMail']. "\r\n";
    $mailData .= 'MIME-Version: 1.0'."\r\n";
    $mailData .= 'Content-Type: text/html; charset="UTF-8"'."\r\n";
    $mailData .= 'Content-Transfer-Encoding: 8bit'."\r\n";
    $mailData .= 'From: "'.$cfg['smtpMailFrom'].'" <'.$cfg['smtpVisionaryMail'].'>'."\r\n";
    $mailData .= 'To: '.$email.' <'.$email.'>'."\r\n";
    $mailData .= 'X-Priority: 3'."\r\n\r\n";
    
    $mailData .= $msg."\r\n";
    
    if(!$socket = fsockopen($cfg['smtpMailHost'], $cfg['smtpMailPort'], $errno, $errstr, 30)) {
        return $errno."&lt;br&gt;".$errstr;
    }
    if (!serverParse($socket, '220', __LINE__)) return false;
    
    fputs($socket, 'HELO '.$cfg['smtpMailHost']. "\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'AUTH LOGIN'."\r\n");
    if (!serverParse($socket, '334', __LINE__)) return false;
    
    fputs($socket, base64_encode($cfg['smtpMailName']) . "\r\n");
    if (!serverParse($socket, '334', __LINE__)) return false;
    
    fputs($socket, base64_encode($cfg['smtpMailPass']) . "\r\n");
    if (!serverParse($socket, '235', __LINE__)) return false;
    
    fputs($socket, 'MAIL FROM: <'.$cfg['smtpVisionaryMail'].'>'."\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'RCPT TO: <'.$email.'>'."\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'DATA'."\r\n");
    if (!serverParse($socket, '354', __LINE__)) return false;
    
    fputs($socket, $mailData."\r\n.\r\n");
    if (!serverParse($socket, '250', __LINE__)) return false;
    
    fputs($socket, 'QUIT'."\r\n");
    
    fclose($socket);
    
    return true;
}

function serverParse($socket, $response, $line = __LINE__) {
    while (substr($server_response, 3, 1) != ' ') {
        if (!($server_response = fgets($socket, 256))) {
            echo 'Error: '.$server_response.', '. $line;
            return false;
        }
    }
    if (!(substr($server_response, 0, 3) == $response)) {
        echo 'Error: '.$server_response.', '. $line;
        return false;
    }
    return true;
}

function getMailTemplate($fileName) {
    $file = get_template_directory().'/mail-templates/'.$fileName.'-'.qtrans_getLanguage().'.html';
    
    if (file_exists($file)) {
        return file_get_contents($file);
    }
    else if (file_exists(str_replace(qtrans_getLanguage(), 'en', $file))) { //Checking if EN file exists
        return file_get_contents(str_replace(qtrans_getLanguage(), 'en', $file));
    }
    else {
        echo 'File <b>'.$fileName.'-'.qtrans_getLanguage().'.html</b> not found under the directory <b>'.$file.'</b><br />';
        return false;
    }
}

function onlineStatus($userTime) {
    if ($userTime+30 >= time()) {
        return true;
    }
    
    return false;
}

function getMonth($month = 1) {
    switch($month) {
        case 1: _e('january', 'pentaclick'); break;
        case 2: _e('february', 'pentaclick'); break;
        case 3: _e('march', 'pentaclick'); break;
        case 4: _e('april', 'pentaclick'); break;
        case 5: _e('may', 'pentaclick'); break;
        case 6: _e('june', 'pentaclick'); break;
        case 7: _e('july', 'pentaclick'); break;
        case 8: _e('august', 'pentaclick'); break;
        case 9: _e('september', 'pentaclick'); break;
        case 10: _e('october', 'pentaclick'); break;
        case 11: _e('november', 'pentaclick'); break;
        case 12: _e('december', 'pentaclick'); break;
    }
    
    return;
}

function approveRegisterTeam($game, $team) {
    //Generating other IDs for different environment
    if (ENV == 'prod') {
        $participant_id = $team->id + 100000;
    }
    else if (ENV == 'test') {
        $participant_id = $team->id + 50000;
    }
    else {
        $participant_id = $team->id;
    }
    
    mysql_query('UPDATE `teams` SET approved = 1 WHERE `tournament_id` = '.(int)cOptions('tournament-'.$game.'-number').' AND `game` = "'.$game.'" AND `id` = '.$team->id);
    mysql_query('UPDATE `players` SET approved = 1 WHERE `tournament_id` = '.(int)cOptions('tournament-'.$game.'-number').' AND `game` = "'.$game.'" AND `team_id` = '.$team->id);
    
    $apiArray = array(
        'participant_id' => $participant_id,
        'participant[name]' => $team->name,
    );
    
    //Adding team to Challonge bracket
    runChallongeAPI('tournaments/pentaclick-'.cOptions('brackets-link-'.$game).'/participants.post', $apiArray);
    
    //Registering ID, becaus Challonge idiots not giving an answer with ID
    $answer = runChallongeAPI('tournaments/pentaclick-'.cOptions('brackets-link-'.$game).'/participants.json');
    array_reverse($answer, true);

    foreach($answer as $f) {
        if ($f->participant->name == $team->name) {
            mysql_query('UPDATE `teams` SET `challonge_id` = '.(int)$f->participant->id.' WHERE `tournament_id` = '.(int)cOptions('tournament-'.$game.'-number').' AND `game` = "'.$game.'" AND `id` = '.$team->id);
            $challonge_id = (int)$f->participant->id;
            break;
        }
    }
    
    sendMail('pentaclickesports@gmail.com',
    ($game=='hs'?'Player':'Team').' added. PentaClick eSports.',
    'Participant was added!!!<br />
    Date: '.date('d/m/Y H:i:s').'<br />'.
    ($game=='hs'?'BattleTag':'TeamName').': <b>'.$team->name.'</b><br>
    IP: '.$_SERVER['REMOTE_ADDR']);
    
    return $challonge_id;
}

function _p($text, $domain) {
    return __($text, $domain);
}

function dump($array) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function ddump($array) {
    dump($array);
    die;
}