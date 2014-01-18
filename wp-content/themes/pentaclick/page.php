<?php
/**
 * @since v1
 */

get_header();
?>

<article class="text-content-wrapper">
    <div class="content" id="text-content">
        <?php while ( have_posts() ) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</article>

<? get_footer();?>

