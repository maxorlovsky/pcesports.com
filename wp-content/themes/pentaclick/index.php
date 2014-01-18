<?php
/**
 * The main template file
 * @package Pentaclick
 * @since v1
 */

get_header();

    /*if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            get_template_part( 'content', get_post_format() );
        endwhile;
        
        twentytwelve_content_nav( 'nav-below' );
    else :*/
    if (is_home()) {
        get_template_part( 'content', 'home' );
        get_template_part( 'content', 'connect' );
        get_template_part( 'content', 'participants' );
        get_template_part( 'content', 'register' );
        get_template_part( 'content', 'format' );
    }
    else {
        get_template_part( 'content', 'none' );
    }
    //endif;

get_footer();

?>