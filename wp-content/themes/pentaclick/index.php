<?php
/**
 * The main template file
 * @package Pentaclick
 * @since v1
 */

$availableGames = array('lol', 'hs'); 
$breakdown = explode('.', $_SERVER['HTTP_HOST']);
$siteData['game'] = $breakdown[0];
if (!in_array($siteData['game'], $availableGames)) {
    $siteData['game'] = '';
}

$category = 'pentaclick';
if (cOptions('game')) {
    $category = cOptions('game');
}

get_header();

if (is_home()) {
    get_template_part( $category, 'home' );

    if (cOptions('brackets-on-lol')) {
        get_template_part( 'lol', 'bracket' );
    }
    if (cOptions('brackets-on-hs')) {
        get_template_part( 'hs', 'bracket' );
    }
    
    if (cOptions('game') == 'lol') {
        get_template_part( 'lol', 'participants' );
        get_template_part( 'lol', 'register' );
        get_template_part( 'lol', 'format' );
        get_template_part( 'lol', 'fame' );
    }
    elseif (cOptions('game') == 'hs') {
        get_template_part( 'hs', 'participants' );
        get_template_part( 'hs', 'register' );
        get_template_part( 'hs', 'format' );
    }
    else {
        //get_template_part( 'pentaclick', 'about' );
        get_template_part( 'pentaclick', 'connect' );
    }
}
else {
    get_template_part( 'content', 'none' );
}
    
get_footer();
?>