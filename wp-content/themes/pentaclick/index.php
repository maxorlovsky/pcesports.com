<?php
/**
 * The main template file
 * @package Pentaclick
 * @since v1
 */

get_header();

if (is_home()) {
    get_template_part( 'content', 'home' );
    if (cOptions('brackets-on')) {
        get_template_part( 'content', 'bracket' );
    }
    get_template_part( 'content', 'connect' );
    get_template_part( 'content', 'participants' );
    get_template_part( 'content', 'register' );
    get_template_part( 'content', 'format' );
}
else {
    get_template_part( 'content', 'none' );
}
    
get_footer();
?>