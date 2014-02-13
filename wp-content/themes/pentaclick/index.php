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

get_header();

if (is_home()) {
    get_template_part( 'pentaclick', 'home' );
    if (cOptions('brackets-on-lol')) {
        get_template_part( 'pentaclick', 'bracket' );
    }
    //get_template_part( 'pentaclick', 'participants' );
    //get_template_part( 'pentaclick', 'register' );
    get_template_part( 'pentaclick', 'about' );
    get_template_part( 'pentaclick', 'connect' );
}
else {
    get_template_part( 'content', 'none' );
}
    
get_footer();
?>