<?php
/**
 * @since v1
 */

get_header();

get_template_part( 'content', 'home' );
get_template_part( 'content', 'connect' );
get_template_part( 'content', 'participants' );
get_template_part( 'content', 'register' );
get_template_part( 'content', 'format' );

get_footer();

?>

			<?php //while ( have_posts() ) : the_post(); ?>
				<?php //get_template_part( 'content', 'page' ); ?>
			<?php //endwhile; // end of the loop. ?>